<?php

/*                        Copyright 2020 Flávio Ribeiro

This file is part of OCOMON.

OCOMON is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

OCOMON is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Foobar; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */session_start();

include_once "../../includes/include_geral.inc.php";
?>
<html>
<script src="../../includes/components/jquery/jquery.js"></script>
<link rel="stylesheet" type="text/css" href="../../includes/components/datatables/datatables.css"/>
<script type="text/javascript" charset="utf8" src="../../includes/components/datatables/datatables.js"></script>
<script type="text/javascript" charset="utf8" src="../../includes/components/datatables/Scroller-2.0.2/js/dataTables.scroller.js"></script>

<link rel="stylesheet" type="text/css" href="../../includes/components/datatables/Responsive-2.2.5/css/responsive.dataTables.css"/>
<script type="text/javascript" charset="utf8" src="../../includes/components/datatables/Responsive-2.2.5/js/dataTables.responsive.min.js"></script>

<body>

<?php

echo "<table id='users-grid' class='display' style='width:100%'>";
    echo "<thead>";
        print "<TR class='header'><td class='line'>" . TRANS('OPT_NAME') . "</TD>" .
        "<td class='line'>" . TRANS('OPT_LOGIN_NAME') . "</TD><td class='line'>" . TRANS('RESPONSIBLE_AREA') . "</TD>" .
        "<td class='line'>" . TRANS('OCO_FIELD_AREA_ADMIN') . "</TD>" .
        "<td class='line'>" . TRANS('OCO_FIELD_SUBSCRIBE_DATE') . "</TD><td class='line'>" . TRANS('HIRE_DATE') . "</TD>" .
        "<td class='line'>" . TRANS('COL_EMAIL') . "</TD><td class='line'>" . TRANS('COL_PHONE') . "</TD>" .
        "<td class='line'>" . TRANS('LEVEL') . "</TD><td class='line'>" . TRANS('BT_ALTER') . "</TD>" .
        "<td class='line'>" . TRANS('BT_REMOVE') . "</TD></TR>";
        echo "</thead>";
        
echo "</table>";



?>
<script type="text/javascript">

$(document).ready(function() {
    var dataTable = $('#users-grid').DataTable( {
        "processing": true,
        "serverSide": true,
        "ajax":{
            url :"users-grid-data.php", // json datasource
            type: "post",  // method  , by default get
            error: function(){  // error handling
                $(".users-grid-error").html("");
                $("#users-grid").append('<tbody class="users-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                $("#users-grid_processing").css("display","none");
                
            }
        },
        dom: "frtiS",
        scrollY: 400,
        deferRender: true,
        scrollCollapse: true,
        scroller: {
            loadingIndicator: true
        },
        responsive: true,
        "language": {
            "url": "../../includes/components/datatables/datatables.pt-br.lang"
        }
    } );
} );

</script>
<?php

print "</body>";
print "</html>";

?>
