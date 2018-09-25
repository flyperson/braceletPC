var EditableTable = function () {

    return {

        //main function to initiate the module
        init: function () {
            function restoreRow(oTable, nRow) {
                var aData = oTable.fnGetData(nRow);
                var jqTds = $('>td', nRow);

                for (var i = 0, iLen = jqTds.length; i < iLen; i++) {
                    oTable.fnUpdate(aData[i], nRow, i, false);
                }

                oTable.fnDraw();
            }

            function editRow(oTable, nRow) {
                var aData = oTable.fnGetData(nRow);
                var jqTds = $('>td', nRow);
                jqTds[0].innerHTML = '<input type="text" class="form-control small" value="' + aData[0] + '">';
                jqTds[1].innerHTML = '<input type="text" class="form-control small" value="' + aData[1] + '">';
                jqTds[2].innerHTML = '<input type="text" class="form-control small" value="' + aData[2] + '">';
                jqTds[3].innerHTML = '<input type="text" class="form-control small" value="' + aData[3] + '">';
                jqTds[4].innerHTML = '<a class="edit" href="">Save</a>';
                jqTds[5].innerHTML = '<a class="cancel" href="">Cancel</a>';
            }

            function saveRow(oTable, nRow) {
                var jqInputs = $('input', nRow);
                oTable.fnUpdate(jqInputs[0].value, nRow, 0, false);
                oTable.fnUpdate(jqInputs[1].value, nRow, 1, false);
                oTable.fnUpdate(jqInputs[2].value, nRow, 2, false);
                oTable.fnUpdate(jqInputs[3].value, nRow, 3, false);
                oTable.fnUpdate('<a class="edit" href="">Edit</a>', nRow, 4, false);
                oTable.fnUpdate('<a class="delete" href="">Delete</a>', nRow, 5, false);
                oTable.fnDraw();
            }

            function cancelEditRow(oTable, nRow) {
                var jqInputs = $('input', nRow);
                oTable.fnUpdate(jqInputs[0].value, nRow, 0, false);
                oTable.fnUpdate(jqInputs[1].value, nRow, 1, false);
                oTable.fnUpdate(jqInputs[2].value, nRow, 2, false);
                oTable.fnUpdate(jqInputs[3].value, nRow, 3, false);
                oTable.fnUpdate('<a class="edit" href="">Edit</a>', nRow, 4, false);
                oTable.fnDraw();
            }

            var oTable = $('#editable-sample').dataTable({
                "aLengthMenu": [
                    [5, 15, 20,30,100, -1],
                    [5, 15, 20,30,100, "All"] // change per page values here
                ],
                // set the initial value
                "iDisplayLength": -1,
                "sDom": "<'row'<'col-lg-6'l><'col-lg-6'f>r>t<'row'<'col-lg-6'i><'col-lg-6'p>>",
                "sPaginationType": "bootstrap",//编页码类型
                "oLanguage": {//四个部件的语言设置
//              	"sUrl": "",//要是json格式

//					"bServerSide": true,
//			        "sAjaxSource": "scripts/post.php",
//			        "sServerMethod": "POST"

                    "sLengthMenu": "_MENU_ 行/页",
                    "oPaginate": {
                    	"sFirst": "首页",
                        "sPrevious": "上一页",
                        "sNext": "下一页",
				        "sLast": "末页",
                    },
                    "sInfo": "本页显示 _START_ 到 _END_ 行 共 _TOTAL_ 行",
                    "sSearch": "查找：",
                    "sEmptyTable": "无数据！",
                    "sInfoEmpty": "本页显示 0 到 0 行 共 0 行",
                    "sLoadingRecords": "加载中...",
                    "sProcessing": "处理中...",
                    "sInfoFiltered": "(在 _MAX_ 行中查找)",
					"sZeroRecords": "没有找到相应数据！",
                },
                "aoColumnDefs": [{//指定那列不进行排序
                        'bSortable': false,
                        'aTargets': [0]
                    }
                ]
            });

            jQuery('#editable-sample_wrapper .dataTables_filter input').addClass("form-control medium"); // modify table search input
            jQuery('#editable-sample_wrapper .dataTables_length select').addClass("form-control xsmall"); // modify table per page dropdown

            var nEditing = null;

            $('#editable-sample_new').click(function (e) {
                e.preventDefault();
//              var aiNew = oTable.fnAddData(['', '', '', '',
//                      '<a class="edit" href="">Edit</a>', '<a class="cancel" data-mode="new" href="">Cancel</a>'
//              ]);
                var aiNew = oTable.fnAddData(['1', '1', '1', '1',
                        '<a class="edit" href="">Edit</a>', '<a class="delete" data-mode="new" href="">Cancel</a>'
                ]);
//              var nRow = oTable.fnGetNodes(aiNew[0]);
//              editRow(oTable, nRow);
//              nEditing = nRow;
            });

            $('#editable-sample a.delete').live('click', function (e) {
                e.preventDefault();

//              if (confirm("Are you sure to delete this row ?") == false) {
//                  return;
//              }

                var nRow = $(this).parents('tr')[0];
                alert(nRow)
                console.log(nRow);
                oTable.fnDeleteRow(nRow);
                alert("Deleted! Do not forget to do some ajax to sync with backend :)");
            });

            $('#editable-sample a.cancel').live('click', function (e) {
                e.preventDefault();
                if ($(this).attr("data-mode") == "new") {
                    var nRow = $(this).parents('tr')[0];
                    oTable.fnDeleteRow(nRow);
                } else {
                    restoreRow(oTable, nEditing);
                    nEditing = null;
                }
            });

            $('#editable-sample a.edit').live('click', function (e) {
                e.preventDefault();

                /* Get the row as a parent of the link that was clicked on */
                var nRow = $(this).parents('tr')[0];

                if (nEditing !== null && nEditing != nRow) {
                    /* Currently editing - but not this row - restore the old before continuing to edit mode */
                    restoreRow(oTable, nEditing);
                    editRow(oTable, nRow);
                    nEditing = nRow;
                } else if (nEditing == nRow && this.innerHTML == "Save") {
                    /* Editing this row and want to save it */
                    saveRow(oTable, nEditing);
                    nEditing = null;
                    alert("Updated! Do not forget to do some ajax to sync with backend :)");
                } else {
                    /* No edit in progress - let's start one */
                    editRow(oTable, nRow);
                    nEditing = nRow;
                }
            });
        }

    };

}();