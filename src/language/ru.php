<?php
return array(
    "bosmenu" => array(
        "feedback" => "Обратная связь"
    ),
    'brick' => array(
        'columnblock' => array(
            "1" => "Обратная связь",
            "2" => "Чтобы оставить сообщение администрации сайта,
	нажмите ссылку \"<a href=\"#\" onclick=\"showFeedbackPanel(); return false;\">отправить сообщение</a>\""
        ),
        'js_data' => array(
            "1" => "Новое сообщение",
            "2" => "
<p>
	Поступило новое сообщение <br />
	Контактное лицо: {v#unm} <br />
	Телефон: {v#phone} <br />
	E-mail: {v#email} <br />
	Сообщение: <br />
	{v#text}
</p>
			
"
        )
    ),
    'content' => array(
        'index' => array(
            "1" => "Обратная связь",
            "2" => "Для отправки сообщения администрации сайта заполните поля формы.",
            "3" => "Контактное лицо",
            "4" => "Телефон",
            "5" => "E-mail",
            "6" => "Сообщение",
            "7" => "Отправить",
            "8" => "Сообщение успешно отправлено"
        )
    )
);
?>