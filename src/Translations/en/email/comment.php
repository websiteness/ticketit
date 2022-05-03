<?php

return [

'data' => '
	<div style="text-align:left; color:#187272">
		Hello :name, <br>
		<br>
		'.config('app.name').' support has replied to the ticket you created. <br>

		Ticket <b>#:id </b><br>
		Title: <b>:subject</b><br>
		Ticket Status: <b> :status</b><br>
		<br>
		Reply from '.config('app.name').' Support:<br> 
		<div>:comment</div><br>
		<br>
		'.config('app.name').' Support <br>
		'.config('constants.support_email').'<br>
	</div>
',

];
