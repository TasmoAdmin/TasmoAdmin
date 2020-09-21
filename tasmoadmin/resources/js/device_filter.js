
function device_filter() {

	// var input, filter, table, tr, td, i, txtValue;
	var input = document.getElementById("filterInput");
	var filter = input.value.toLowerCase();
	
	var table = document.getElementById("device-list");
	var tr = table.getElementsByTagName("tr");

	for (var i = 0; i < tr.length; i++) {
		var td = tr[i].getElementsByTagName("td")[3];

		if (td) {
			var txtValue = td.innerText.trim().replace(/\s+/g, ' ');
		
			if (txtValue.toLowerCase().indexOf(filter) > -1) {
				tr[i].style.display = "";
			} else {
				tr[i].style.display = "none";
			}
		}
	}
}
