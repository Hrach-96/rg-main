function mmwindow()
{
	if($("#editor").height() > 25)
	{
		$("#editor").height(25);
		$("#editor").width(25);
		$("#editMM").html("+");
	}else{
		$("#editor").height(350);
		$("#editor").width(350);
		$("#editMM").html("-");
	}
}
function getEditors(){
	$.get("pull.php?editors", function (get_data){
		var editors_data = "";
		for(var k = 0;k < get_data.length;k++)
		{
			var eNum = k+1;
			editors_data += '<div id="editorMessage"><strong>'+eNum+'.</strong>OPERATOR:<strong>'+get_data[k].operator+'</strong> EDITING:<strong>'+get_data[k].pid+'</strong></div>';
		}
		$("#dataMsg").html(editors_data);
	});
}
//getEditors();
//setInterval(getEditors,8000);