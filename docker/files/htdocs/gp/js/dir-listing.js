// This is the lighttpd standard dirlisting script.


var click_column;
var name_column = 0;
var date_column = 1;
var size_column = 2;
var type_column = 3;
var prev_span = null;

if (typeof (String.prototype.localeCompare) === 'undefined') {
  String.prototype.localeCompare = function (str, locale, options) {
    return ((this == str) ? 0 : ((this > str) ? 1 : -1));
  };
}

if (typeof (String.prototype.toLocaleUpperCase) === 'undefined') {
  String.prototype.toLocaleUpperCase = function () {
    return this.toUpperCase();
  };
}

function get_inner_text(el) {
  if ((typeof el == 'string') || (typeof el == 'undefined'))
    return el;
  if (el.innerText)
    return el.innerText;
  else {
    var str = "";
    var cs = el.childNodes;
    var l = cs.length;
    for (var i = 0; i < l; i++) {
      if (cs[i].nodeType == 1) str += get_inner_text(cs[i]);
      else if (cs[i].nodeType == 3) str += cs[i].nodeValue;
    }
  }
  return str;
}

function isdigit(c) {
  return (c >= '0' && c <= '9');
}

function unit_multiplier(unit) {
  return (unit == 'K') ? 1000
    : (unit == 'M') ? 1000000
      : (unit == 'G') ? 1000000000
        : (unit == 'T') ? 1000000000000
          : (unit == 'P') ? 1000000000000000
            : (unit == 'E') ? 1000000000000000000 : 1;
}

var li_date_regex = /(\d{4})-([\d\w]{2,3})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/;

var li_mon = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
  'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

function li_mon_num(mon) {
  var i; for (i = 0; i < 12 && mon != li_mon[i]; ++i); return i;
}

function li_date_cmp(s1, s2) {
  var dp1 = li_date_regex.exec(s1)
  var dp2 = li_date_regex.exec(s2)
  for (var i = 1; i < 7; ++i) {
    var cmp = (2 != i)
      ? parseInt(dp1[i]) - parseInt(dp2[i])
      : li_mon_num(dp1[2]) - li_mon_num(dp2[2]);
    if (0 != cmp) return cmp;
  }
  return 0;
}

function sortfn_then_by_name(a, b, sort_column) {
  if (sort_column == name_column || sort_column == type_column) {
    var ad = (a.cells[type_column].innerHTML === 'Directory');
    var bd = (b.cells[type_column].innerHTML === 'Directory');
    if (ad != bd) return (ad ? -1 : 1);
  }
  var at = get_inner_text(a.cells[sort_column]);
  var bt = get_inner_text(b.cells[sort_column]);
  var cmp;
  if (sort_column == name_column) {
    if (at == '../') return -1;
    if (bt == '../') return 1;
  }
  if (a.cells[sort_column].className == 'int') {
    cmp = parseInt(at) - parseInt(bt);
  } else if (sort_column == date_column) {
    var ad = isdigit(at.substr(0, 1));
    var bd = isdigit(bt.substr(0, 1));
    if (ad != bd) return (!ad ? -1 : 1);
    cmp = li_date_cmp(at, bt);
  } else if (sort_column == size_column) {
    var ai = parseInt(at, 10) * unit_multiplier(at.substr(-1, 1));
    var bi = parseInt(bt, 10) * unit_multiplier(bt.substr(-1, 1));
    if (at.substr(0, 1) == '-') ai = -1;
    if (bt.substr(0, 1) == '-') bi = -1;
    cmp = ai - bi;
  } else {
    cmp = at.toLocaleUpperCase().localeCompare(bt.toLocaleUpperCase());
    if (0 != cmp) return cmp;
    cmp = at.localeCompare(bt);
  }
  if (0 != cmp || sort_column == name_column) return cmp;
  return sortfn_then_by_name(a, b, name_column);
}

function sortfn(a, b) {
  return sortfn_then_by_name(a, b, click_column);
}

function resort(lnk) {
  var span = lnk.childNodes[1];
  var table = lnk.parentNode.parentNode.parentNode.parentNode;
  var rows = new Array();
  for (var j = 1; j < table.rows.length; j++)
    rows[j - 1] = table.rows[j];
  click_column = lnk.parentNode.cellIndex;
  rows.sort(sortfn);

  if (prev_span != null) prev_span.innerHTML = '';
  if (span.getAttribute('sortdir') == 'down') {
    span.innerHTML = '&uarr;';
    span.setAttribute('sortdir', 'up');
    rows.reverse();
  } else {
    span.innerHTML = '&darr;';
    span.setAttribute('sortdir', 'down');
  }
  for (var i = 0; i < rows.length; i++)
    table.tBodies[0].appendChild(rows[i]);
  prev_span = span;
}

function init_sort(init_sort_column, ascending) {
  var tables = document.getElementsByTagName("table");
  for (var i = 0; i < tables.length; i++) {
    var table = tables[i];
    //var c = table.getAttribute("class")
    //if (-1 != c.split(" ").indexOf("sort")) {
    var row = table.rows[0].cells;
    for (var j = 0; j < row.length; j++) {
      var n = row[j];
      if (n.childNodes.length == 1 && n.childNodes[0].nodeType == 3) {
        var link = document.createElement("a");
        var title = n.childNodes[0].nodeValue.replace(/:$/, "");
        link.appendChild(document.createTextNode(title));
        link.setAttribute("href", "#");
        link.setAttribute("class", "sortheader");
        link.setAttribute("onclick", "resort(this);return false;");
        var arrow = document.createElement("span");
        arrow.setAttribute("class", "sortarrow");
        arrow.appendChild(document.createTextNode(":"));
        link.appendChild(arrow)
        n.replaceChild(link, n.firstChild);
      }
    }
    var lnk = row[init_sort_column].firstChild;
    if (ascending) {
      var span = lnk.childNodes[1];
      span.setAttribute('sortdir', 'down');
    }
    resort(lnk);
    //}
  }
}

function init_sort_from_query() {
  var urlParams = new URLSearchParams(location.search);
  var c = 0;
  var o = 0;
  switch (urlParams.get('C')) {
    case "N": c = 0; break;
    case "M": c = 1; break;
    case "S": c = 2; break;
    case "T":
    case "D": c = 3; break;
  }
  switch (urlParams.get('O')) {
    case "A": o = 1; break;
    case "D": o = 0; break;
  }
  init_sort(c, o);
}

$(function(){
  init_sort_from_query();
  $("div.list tr").on("mousedown", function(ev){
    if(ev.buttons != 1) return;
    const link = $(this).find("td.n a").attr("href");
    if(!link) return;
    location.href = link;
  });
})
