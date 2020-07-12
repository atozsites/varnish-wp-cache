// our id for our rows, we can have rows added and removed
// so we need a base
var rowCount = 0;

function createRow(tableID, id, addr, port, secret) {
	var row = document.createElement ('tr');
	var td1 = document.createElement ('td');
	var td2 = document.createElement ('td');
	var td3 = document.createElement ('td');
	var td4 = document.createElement ('td');
	var Bestwebsite_addr = document.createElement ('input');
	var Bestwebsite_port = document.createElement ('input');
	var Bestwebsite_secret = document.createElement ('input');
	var dRow = document.createElement ('input');

	Bestwebsite_addr.className = "regular-text";
	Bestwebsite_addr.type = "text";
	Bestwebsite_addr.id = id;
	Bestwebsite_addr.name = "BestwebsiteVarnish_addr[]";
	Bestwebsite_addr.value = addr || "";

	Bestwebsite_port.className = "small-text";
	Bestwebsite_port.type = "text";
	Bestwebsite_port.id = id;
	Bestwebsite_port.name = "BestwebsiteVarnish_port[]";
	Bestwebsite_port.value = port || "";

	Bestwebsite_secret.className = "regular-text";
	Bestwebsite_secret.type = "text";
	Bestwebsite_secret.id = id;
	Bestwebsite_secret.name = "BestwebsiteVarnish_secret[]";
	Bestwebsite_secret.value = secret || "";

	dRow.className = "";
	dRow.type = "button";
	dRow.name = "deleteRow";
	dRow.value = "-";
	dRow.id = id;
	dRow.onclick = function () { deleteRow(tableID, id); }

	td1.appendChild (Bestwebsite_addr);
	td2.appendChild (Bestwebsite_port);
	td3.appendChild (Bestwebsite_secret);
	td4.appendChild (dRow);
	row.appendChild (td1);
	row.appendChild (td2);
	row.appendChild (td3);
	row.appendChild (td4);

	return row;
}

function addRow(tableID, id, addr, port, secret) {
	var tbody = document.getElementById(tableID).getElementsByTagName ('tbody')[0];

	rowCount++;
	var row = createRow(tableID, id, addr, port, secret);

	tbody.appendChild (row);
}

function deleteRow(tableID, rowID) {
	try {
		var tbody = document.getElementById(tableID).getElementsByTagName ('tbody')[0];
		var trs = tbody.getElementsByTagName ('tr');

		// the id = 0 we don't want to remove, as it is the header
		for (var i = 1; i < trs.length; i++) {
			// we use our own id, let's not mix up with table ids
			var id = (trs[i].getElementsByTagName ('input')[0]).id;
			if (id == rowID) {
				tbody.deleteRow (i);
				return;
			}
		}
	} catch(e) {
		alert(e);
	}
}
