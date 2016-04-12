<?php
    function print_comments(&$comments, &$controller, $parent = 0)
    {
        if (empty($comments[$parent])) return;
            echo '<ul class="сomment-box">';
        for ($i = 0;$i < count($comments[$parent]);$i++)
        { ?>
        <li class="comment js-item-comment" id="<?php echo 'cm_' . $comments[$parent][$i]->id; ?>">
            <section class="comment__content">
                <header class="comment__header">
                    <a href="/<?php echo $controller; ?>/del/id/<?php echo $comments[$parent][$i]->id; ?>/level/<?php echo $comments[$parent][$i]->level; ?>"
                       class="button-icon" title="Удалить"></a>

                    <h2 class="comment__title"><?php echo $comments[$parent][$i]->user_name; ?> <span
                            class="comment__date"><?php echo date('m.d.y H:i', $comments[$parent][$i]->publication_date); ?></span></h2>
                </header>
                <article class="comment__text"><?php echo $comments[$parent][$i]->content; ?></article>
                <footer class="comment__footer">
                    <a class="button js-form-add"
                       href="/comments/add/parent/<?php echo $comments[$parent][$i]->id; ?>/level/<?php echo $comments[$parent][$i]->level; ?>#cm_<?php echo $comments[$parent][$i]->id; ?>">Комментировать</a>
                </footer>
            </section>
            <?php
            print_comments($comments, $controller, $comments[$parent][$i]->id);
        echo "</li>";
        }
        echo "</ul>";
    }
    if(count($comments)!=0){
        print_comments($comments, $controller);
    }
    echo '<div id="another"><a class="button buttion_center" href="/comments/add/parent/-1/level/0#cm_add">Комментировать</a></div>';?>

    <template id="comment-add-template" style="display: none;">
        <form class="form-main"  method="POST" class="form">
            <ol class="forma-main__item">
            </ol>

            <div class="forma-main__item">
                <label for="name" class="form-main__title">Ваше имя:</label> <br/>
                <input class="form-main__input" id="name" name="name" type="text" required  /><br/>
            </div>
            <div class="forma-main__item">
                <label for="text" class="form-main__title">Текст комментария:</label> <br/>
                <textarea class="form-main__input form-main__input_text" id="text" name="text"  required ></textarea> <br/>
            </div>
            <div class="forma-main__item">
                <input class="button" type="submit" value="Отправить"/>
            </div>
        </form>
    </template>
    <template id="comment-template" style="display: none;">
        <li class="comment js-item-comment">
            <section class="comment__content">
                <header class="comment__header">
                    <a href="" class="button-icon" title="Удалить"></a>
                    <h2 class="comment__title">temp<span class="comment__date"></span></h2>
                </header>
                <article class="comment__text"></article>
                <footer class="comment__footer">
                    <a class="button js-form-add" href="">Комментировать</a>
                </footer>
            </section>
        </li>
    </template>

<?php ?>


