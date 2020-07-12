// our id for our rows, we can have rows added and removed
// so we need a base
var rowCount = 0;

function createRow(tableID, id, addr, port, secret) {
	var row = document.createElement ('tr');
	var td1 = document.createElement ('td');
	var td2 = document.createElement ('td');
	var td3 = document.createElement ('td');
	var td4 = document.createElement ('td');
	var atozsites_addr = document.createElement ('input');
	var atozsites_port = document.createElement ('input');
	var atozsites_secret = document.createElement ('input');
	var dRow = document.createElement ('input');

	atozsites_addr.className = "regular-text";
	atozsites_addr.type = "text";
	atozsites_addr.id = id;
	atozsites_addr.name = "atozsitesVarnish_addr[]";
	atozsites_addr.value = addr || "";

	atozsites_port.className = "small-text";
	atozsites_port.type = "text";
	atozsites_port.id = id;
	atozsites_port.name = "atozsitesVarnish_port[]";
	atozsites_port.value = port || "";

	atozsites_secret.className = "regular-text";
	atozsites_secret.type = "text";
	atozsites_secret.id = id;
	atozsites_secret.name = "atozsitesVarnish_secret[]";
	atozsites_secret.value = secret || "";

	dRow.className = "";
	dRow.type = "button";
	dRow.name = "deleteRow";
	dRow.value = "-";
	dRow.id = id;
	dRow.onclick = function () { deleteRow(tableID, id); }

	td1.appendChild (atozsites_addr);
	td2.appendChild (atozsites_port);
	td3.appendChild (atozsites_secret);
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
