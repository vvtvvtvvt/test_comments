<?php
    function print_comments(&$comments, &$controller, &$parent_add, &$err, $parent = 0)
    {

            echo '<ul class="сomment-box">';

        for ($i = 0;
        $i < count($comments[$parent]);
        $i++)
        { ?>
        <li class="comment" id="<?php echo 'cm_' . $comments[$parent][$i]->id; ?>">
            <section class="comment__content">
                <header class="comment__header">
                    <a href="/<?php echo $controller; ?>/del/id/<?php echo $comments[$parent][$i]->id; ?>/level/<?php echo $comments[$parent][$i]->level; ?>"
                       class="button-icon" title="Удалить"></a>
                    <h2 class="comment__title"><?php echo $comments[$parent][$i]->user_name; ?> <span
                            class="comment__date"><?php echo date('m.d.y H:i', $comments[$parent][$i]->publication_date);?></span></h2>
                </header>
                <article class="comment__text"><?php echo $comments[$parent][$i]->content; ?></article>
                <footer class="comment__footer">
                    <?php if($parent_add!=$comments[$parent][$i]->id){ ?>
                    <a class="button"
                       href="/comments/add/parent/<?php echo $comments[$parent][$i]->id; ?>/level/<?php echo $comments[$parent][$i]->level; ?>">Комментировать</a>
                    <?php } else {?>
                    <form class="form-main"  method="POST" class="form">
                        <?php if(count($err)!=0){  ?>
                        <ol class="forma-main__item">
                            <?php foreach($err as $item){
                            echo "<li class='form-main__err'> <span class='form-main__err-text'>Ошбика: ".$item."</span></li>";
                            } ?>
                        </ol>
                        <?php } ?>
                        <div class="forma-main__item">
                            <label for="name" class="form-main__title">Ваше имя:</label> <br/>
                            <input class="form-main__input" id="name" name="name" type="text" value="<?php if(!is_null($_POST['name'])) echo $_POST['name']; ?>"/><br/>
                        </div>
                        <div class="forma-main__item">
                            <label for="text" class="form-main__title">Текст комментария:</label> <br/>
                            <textarea class="form-main__input form-main__input_text" id="text" name="text" ><?php if(!is_null($_POST['name'])) echo $_POST['name']; ?></textarea> <br/>
                        </div>
                        <div class="forma-main__item">
                            <input class="button" type="submit" value="Отправить"/>
                        </div>
                    </form>
                    <?php } ?>
                </footer>
            </section>
            <?php
            print_comments($comments, $controller, $parent_add, $err, $comments[$parent][$i]->id);

        echo "</li>";
        }
        echo "</ul>";
    }


    print_comments($comments, $controller, $parent, $err);

    if($parent<0){?>
        <form id="cm_add" class="form-main" method="POST" class="form">
            <?php if(count($err)!=0){  ?>
                <ol class="forma-main__item">
                    <?php foreach($err as $item){
                        echo "<li class='form-main__err'> <span class='form-main__err-text'>Ошбика: ".$item."</span></li>";
                    } ?>
                </ol>
            <?php } ?>
            <div class="forma-main__item">
                <label for="name" class="form-main__title">Ваше имя:</label> <br/>
                <input class="form-main__input" id="name" name="name" type="text" value="<?php if(!is_null($_POST['name'])) echo $_POST['name']; ?>"/><br/>
            </div>
            <div class="forma-main__item">
                <label for="text" class="form-main__title">Текст комментария:</label> <br/>
                <textarea class="form-main__input form-main__input_text" id="text" name="text" ><?php if(!is_null($_POST['name'])) echo $_POST['name']; ?></textarea> <br/>
            </div>
            <div class="forma-main__item">
                <input class="button" type="submit" value="Отправить"/>
            </div>
        </form>
    <?php }
?>


