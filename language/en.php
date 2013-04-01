<?php
return array(
	'brick' => array(
		'columnblock' => array(
			"1" => "Feedback",
			"2" => "To leave a message for a site, click the \"<a href=\"#\" onclick=\"showFeedbackPanel(); return false;\">send a message</a>\""
		)
,
		'js_data' => array(
			"1" => "New Message",
			"2" => "
<p>
	Received new <br />
	Contact name: {v#unm} <br />
	Phone: {v#phone} <br />
	E-mail: {v#email} <br />
	Message: <br />
	{v#text}
</p>
"
		)

	)
,
	'content' => array(
		'index' => array(
			"1" => "Feedback",
			"2" => "To send a message to the administration of the site fill in the form.",
			"3" => "Contact name",
			"4" => "Phone",
			"5" => "E-mail",
			"6" => "Message",
			"7" => "Send",
			"8" => "Message has been sent"
		)

	)
);
?>