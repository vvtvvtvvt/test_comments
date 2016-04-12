'use strict';

(function(){
    //название библиотеки
    window.my_obj={};
    //вспомогательная фунция
    function insertAfter(elem, refElem) {
        var parent = refElem.parentNode;
        var next = refElem.nextSibling;
        if (next) {
            return parent.insertBefore(elem, next);
        } else {
            return parent.appendChild(elem);
        }
    }
    //описывает работу с деревом комметариев
    function Tree_comments (container_id){
        this.container = document.getElementById(container_id);//контейнер который хранит дерево комментариев
        this.root = [];//массив комметариев 0 уровня

        //обработчики событий
        this._on_add = this._on_add.bind(this); //хранит обработчик добавления комменатрия

        //подключение обработчиков
        this.container.addEventListener('click', this._on_add);
    }

    //обработчик события добавления комментария
    Tree_comments.prototype._on_add = function(evt) {
        if(evt.target.classList.contains("js-form-add")){
            this.goTree(function(item){item.delete_add_form();}, this.root);
       }
    };

    // обходит дерево и над каждым элементом выполняет funk_wokr
    Tree_comments.prototype.goTree = function (funk_wokr, items) {
        for (var i = 0; i < items.length; i++){
            funk_wokr(items[i]);
            this.goTree(funk_wokr,items[i].suns);
        }
    };
    //Читает html и заполняет дерево элементами items - читаемый узел документа, nodes текущий узел дерева, level - уровень
    Tree_comments.prototype.readTreeOnPage = function(items, nodes, level) {
        if(items instanceof HTMLCollection) {
            for (var i = 0; i < items.length; i++) {
                nodes.push(new Comment(items[i], level));
                if(items[i].lastElementChild!=items[i].firstElementChild)
                    this.readTreeOnPage(items[i].lastElementChild.children, nodes[nodes.length - 1].suns, (level+1));
            }
        }
    };
    // описывает элемент дерева сообщений
    function Comment(data, level){
        this.elem = data;// отображение в документе
        this.suns = []; //дочерние элементы
        this.level=level; // уровень сложенности
        this.id = data.id.substr(3); // идентификатор
        this.status_add=false;
        this.close = false;
        // кнопки
        this._del_btn = this.elem.firstElementChild.firstElementChild.firstElementChild; //удаление сообщения
        this._add_btn = this.elem.firstElementChild.lastElementChild.firstElementChild; // открытие формы добавления
        if(level>=5){
            this._add_btn.parentNode.removeChild(this._add_btn);
            this._add_btn=undefined;
        }
        this._form_add; //форма для отарвки сообщения
        this._post_form; // кнопка отправки формы
        if(this.elem.firstElementChild != this.elem.lastElementChild){ //если есть дочерние элементы, то добавить кнопку свернуть
            this.close=true;
            var btn = document.createElement("button");
            btn.classList.add("button-icon");
            btn.classList.add("button-icon_image_roll");
            btn.title = "Свернуть комментарии";
            insertAfter(btn, this._del_btn);
            this._close_btn = btn;//кнопка свернуть
        }
        // обработчики событий
        this._on_close = this._on_close.bind(this); // свернуть
        this._on_delete = this._on_delete.bind(this); // удалить
        this._on_add = this._on_add.bind(this); // открыть форму добавления
        this.on_post_form = this.on_post_form.bind(this); // отправка формы

        // подсключение обработчиков событий
        if(this.close){ //если есть кнопка свернуть
            this._close_btn.addEventListener('click', this._on_close);
        }
        this._del_btn.addEventListener('click', this._on_delete);// удаление
        if(level<5) {
            this._add_btn.addEventListener('click', this._on_add);// открыть форму добавления
        }

    }

    //обработчик отправки формы добавления комментария
    Comment.prototype.on_post_form = function(evt) {
        evt.preventDefault();
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'comments/add/ajax/1/level/'+this.level.toString()+'/parent/'+this.id.toString());
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded'); // Отправляем кодировку
        xhr.timeout = 10000;
        var text =this._form_add.firstElementChild.nextElementSibling.nextElementSibling.lastElementChild.previousElementSibling.value;
        var name =this._form_add.firstElementChild.nextElementSibling.lastElementChild.previousElementSibling.value;
        xhr.onerror = function(evt){
            this._post_form.value="Отправить";
            var err_list = this._form_add.firstElementChild;
            while(err_list.childNodes[0]){
                err_list.removeChild(err_list.childNodes[0]);
            }

            var err = document.createElement("li");
            err.classList.add("form-main__err");
            var err_text = document.createElement("span");
            err_text.classList.add("form-main__err-text");
            err_text.textContent = "Не удалось отправить комментарий. Возможно у Вас отлючён интернет, или сервер перегружен.";
            err.appendChild(err_text);
            err_list.appendChild(err);

        }.bind(this);
        xhr.ontimeout = function() {
            this._post_form.value="Отправить";
            var err_list = this._form_add.firstElementChild;
            while(err_list.childNodes[0]){
                err_list.removeChild(err_list.childNodes[0]);
            }

            var err = document.createElement("li");
            err.classList.add("form-main__err");
            var err_text = document.createElement("span");
            err_text.classList.add("form-main__err-text");
            err_text.textContent = "Неизвестная ошибка.";
            err.appendChild(err_text);
            err_list.appendChild(err);
        }.bind(this);
        xhr.onload = function(evt) {
            this._post_form.value="Отправить";
            var rawData = evt.target.response;
            var answer = JSON.parse(rawData);

            if(answer.answer=="yes") {
                var template = document.getElementById("comment-template");
                var comment = 'content' in template ?
                    template.content.children[0].cloneNode(true) :
                    template.children[0].cloneNode(true);
                comment.id="cm_"+answer.last_id;
                comment.firstElementChild.firstElementChild.lastElementChild.firstChild.textContent = name+" ";
                comment.firstElementChild.firstElementChild.lastElementChild.firstElementChild.textContent = answer.date;
                comment.firstElementChild.firstElementChild.nextElementSibling.textContent = text;
                if(this.elem.firstElementChild.nextElementSibling!==null){
                   this.elem.firstElementChild.nextElementSibling.insertBefore(comment, this.elem.firstElementChild.nextElementSibling.firstElementChild);
                }
                else{
                    var box = document.createElement("ul");
                    box.classList.add("сomment-box");
                    box.appendChild(comment);
                    this.elem.appendChild(box);
                }

                this.delete_add_form();
                this.suns.unshift(new Comment(document.getElementById("cm_"+answer.last_id), this.level+1));
                if(!this.close) {
                    this.close=true;
                    var btn = document.createElement("button");
                    btn.classList.add("button-icon");
                    btn.classList.add("button-icon_image_roll");
                    btn.title = "Свернуть комментарии";
                    insertAfter(btn, this._del_btn);
                    this._close_btn = btn;
                    this._close_btn.addEventListener('click', this._on_close);
                }
            }
            else{
                var err_list = this._form_add.firstElementChild;
                while(err_list.childNodes[0]){
                    err_list.removeChild(err_list.childNodes[0]);
                }
                for(var i=0; i<answer.err.length;++i){
                    var err = document.createElement("li");
                    err.classList.add("form-main__err");
                    var err_text = document.createElement("span");
                    err_text.classList.add("form-main__err-text");
                    err_text.textContent = answer.err[i];
                    err.appendChild(err_text);
                    err_list.appendChild(err);
                }
            }

        }.bind(this);

        xhr.send("text=" + encodeURIComponent(text) + "&name=" + encodeURIComponent(name)); // Отправляем POST-запрос
        this._post_form.value="Ожидайте";

    };

    //отвязывает все события от элемента
    Comment.prototype.del_events = function() {
        if(this.close) {
            this._close_btn.removeEventListener('click', this._on_close);
        }
        this._del_btn.removeEventListener('click', this._on_delete);
        if(this.level<5) {
            this._add_btn.removeEventListener('click', this._on_add);
        }
        if(this.status_add){}
    };
    //привязывает события
    Comment.prototype.add_events = function() {
        if(this.close) {
            this._close_btn.addEventListener('click', this._on_close);
        }
        this._del_btn.addEventListener('click', this._on_delete);
        if(this.level<5) {
            this._add_btn.addEventListener('click', this._on_add);
        }
        if(this.status_add){}
    };

    //сворачивае/открывает элемент
    Comment.prototype._on_close = function(evt, not_start) {
        if(this.close) {
            if (this._close_btn.classList.contains("js-button-icon_open")) {
                if (this.elem.firstElementChild != this.elem.lastElementChild) {
                    if (not_start != true) {
                        this.elem.lastElementChild.classList.remove("js-close");
                        this._close_btn.classList.remove("js-button-icon_open");
                        this._close_btn.title = "Свернуть комментарии";
                    }

                    /*this.suns.forEach(function (item) {
                        item.add_events();
                        item._on_close(evt, true);
                    });*/
                }
            }
            else {
                if (this.elem.firstElementChild != this.elem.lastElementChild) {
                    if (not_start != true) {
                        this.elem.lastElementChild.classList.add("js-close");
                        this._close_btn.classList.add("js-button-icon_open");
                        this._close_btn.title = "Раскрыть комментарии";
                    }

                    /*this.suns.forEach(function (item) {
                        item.del_events();
                        item._on_close(evt, true);
                    });*/
                }
            }
        }
    };
    Comment.prototype._on_delete_tree = function(evt) {
        this.del_events(evt);
        this.suns.forEach(function(item){
            item._on_delete_tree(evt);
        });
    };
    //обработчик события удаления
    Comment.prototype._on_delete = function(evt) {
        evt.preventDefault();
        if (confirm("Вы точно хотите удалить эту ветку собщений?")){
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'comments/del/ajax/1/level/'+this.level.toString()+'/id/'+this.id.toString());
            xhr.timeout = 30000;
            xhr.onerror = function(evt){
                alert("Ошибка. Не удалось удалить сообщения");
            };
            xhr.ontimeout = function(evt) {
                alert("Ошибка. Не удалось удалить сообщения");
            };
            xhr.onload = function(evt){
                var rawData = evt.target.response;
                if(rawData=="yes"){
                    this._on_delete_tree();
                    this.elem.parentNode.removeChild(this.elem);
                }
                else{
                    alert("Ошибка: Не удалось удалить");
                }
            }.bind(this);
            var rawData = evt.target.response;
            xhr.send();
        }
            this.elem.firstElementChild.firstElementChild.firstElementChild.nextElementSibling.removeEventListener('click', this.on_close);
    };
    // обработчик события открытия формы
    Comment.prototype._on_add = function(evt) {
        evt.preventDefault();
        var template = document.getElementById("comment-add-template");
        this._form_add = 'content' in template ?
            template.content.children[0].cloneNode(true) :
            template.children[0].cloneNode(true);
        this._add_btn.classList.add("js-close");
        this._add_btn.classList.add("js-close-temp");
        this._add_btn.parentNode.appendChild(this._form_add);
        this._post_form = this._form_add.lastElementChild.firstElementChild;
        this._post_form.addEventListener('click', this.on_post_form);
    };
    //закрывает форму добавления комментария
    Comment.prototype.delete_add_form = function () {
        if(typeof this._form_add  != "undefined"){
            if(this._add_btn.classList.contains("js-close-temp")){
                this._add_btn.classList.remove("js-close-temp");
            }else {
                this._add_btn.classList.remove("js-close");
                this._post_form.removeEventListener('click', this.on_post_form);
                this._add_btn.parentNode.removeChild(this._form_add);
                this._form_add = undefined;
            }
        }
    };
    //Обновляет данные об отображении
    Comment.prototype.update = function(evt) {
        this.elem = document.getElementById("cm_"+this.id);
        if(this.close){ //если есть кнопка свернуть
            this._close_btn.removeEventListener('click', this._on_close);
            this._close_btn.addEventListener('click', this._on_close);
        }
    };
    // добавления класса в библиотеку
    window.my_obj.Tree_comments = Tree_comments;
}());




var test = new window.my_obj.Tree_comments("container_cm");
test.readTreeOnPage(test.container.firstElementChild.children, test.root, 1);







