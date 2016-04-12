'use strict';

function Tree_comments(container_id){
    var container = document.getElementById(container_id);
    ReadTreeOnPage(container.firstChild.childNodes);
}
Tree_comments.prototype.ReadTreeOnPage = function(items) {
    items.forEach(function(item) {
       console.log(item);
    })
    /*for (var i = 0; i <items.length; i++) {
        alert( document.body.childNodes[i] ); // Text, DIV, Text, UL, ..., SCRIPT
    }*/
};

var test = Tree_comments("container_cm");