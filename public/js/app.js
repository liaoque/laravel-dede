$('.webhaeder').click(function (e) {
    e.preventDefault();
    e.stopPropagation();
    location.href = $(this).data('href');
});

function CheckCross(self) {
    $(self).val() == 2 ? $('#crossid').show() : $('#crossid').hide();
}

function CheckPathSet(self) {
    var surl = document.getElementById("siteurl");
    var sreid = document.getElementById("reid");
    if (!CheckPathSet.old) {
        CheckPathSet.old = $('input[name="referpath"]:checked').get(0);
    }
    var mysel = $('input[name="referpath"]').get(2);
    if (surl.value != "") {
        if (sreid.value == "0" || sreid.value == "") {
            mysel.checked = true;
            return;
        }
    }
    CheckPathSet.old.checked = true;
    CheckPathSet.old = null;
}

function ShowHide(objname) {
    var obj = document.getElementById(objname);
    if (obj.style.display != "none")
        obj.style.display = "none";
    else {
        obj.style.display = "block";
    }
}

function checkSubmit() {
    if (document.form1.typename.value == "") {
        alert("栏目名称不能为空！");
        document.form1.typename.focus();
        return false;
    }
    return true;
}


function CheckTypeDir() {
    var upinyin = document.getElementById('upinyin');
    var tpobj = document.getElementById('typedir');
    if (upinyin.checked) tpobj.setAttribute('disabled', true);
    else tpobj.removeAttribute('disabled');
}

function moveShow(e, obj) {
    e.preventDefault();
    e.stopPropagation();
    var modalId = $(obj).data('target');
    var title = $(obj).data('title');
    var channeltype = $(obj).data('channeltype');
    $(modalId + ' #nav').text(title);
    var movetype = $(modalId + ' .movetype').empty();
    $('<option value=0>移动为顶级栏目</option>').appendTo(movetype);
    for (var i = 0; i < navListArrayOne.length; i++) {
        if (navListArrayOne[i].channeltype == channeltype) {
            $('<option>').text(navListArrayOne[i]._typename).val(navListArrayOne[i].id).appendTo(movetype);
        }
    }
    $(modalId + ' input[name="id"]').val($(obj).data('id'));
    $(modalId).modal('toggle');
}


$('.modal .btn-ajax').click(function () {
    var self = $(this);
    var form = self.parents('form');
    var ation = form.attr('action');
    var data = form.serializeArray();
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        type: "POST",
        url: ation,
        data: data,
        success: function (data) {
            if (data.message == 'success') {
                showTip('成功', '操作成功', 1);
                $('body').trigger('delete', [1]);
                self.parents('.modal').modal('toggle');
            } else {
                $('body').trigger('delete', [0]);
            }
        },
        error: function (error, data2, data3) {
            var desc = [];
            for (var k in error.responseJSON.errors) {
                desc.push(k + ':' + error.responseJSON.errors[k]);
            }
            showTip(error.status, desc.join('<br/>'), 0)
            $('body').trigger('delete', [0]);
        }

    });
});

function showTip(title, desc, s) {
    if (s) {
        $('#modal-alert .modal-title').text(title);
        $('#modal-alert .modal-body').html('<p class="text-green">' + desc + '</p>');
    } else {
        $('#modal-alert .modal-title').text(title);
        $('#modal-alert .modal-body').html('<p class="text-red">' + desc + '</p>');
    }
    $('#modal-alert').modal('toggle');
}

$('#modal-confirm').on('hide.bs.modal', function (event) {
    $('body').trigger('delete', [0]);
})

function deleteItem(e, obj) {
    e.preventDefault();
    e.stopPropagation();
    var self = $(obj);
    var name = self.data('name');
    var action = self.data('action');
    var id = self.data('id');
    $('#modal-confirm input[name="id"]').val(id);
    $('#modal-confirm form').attr('action', action);
    var _title = "你要确实要删除栏目： [" + name + "] 吗？";
    $('#modal-confirm .modal-body').html('<p class="text-red">' + _title + '</p>');
    $('#modal-confirm').modal('toggle');
    $('body').one('delete', function (event, flag) {
        if (flag) {
            self.parents('.treeview').remove();
        }
    });
}


function sortrank(e, obj) {
    var self = $(obj);
    var form = self.parents('form');
    var url = form.attr('action');
    var data = form.serialize();
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        type: "POST",
        url: url,
        data: data,
        success: function (data) {
            if (data.message == 'success') {
                showTip('成功', '操作成功', 1);
            }
        },
        error: function (error, data2, data3) {
            var desc = [];
            for (var k in error.responseJSON.errors) {
                desc.push(k + ':' + error.responseJSON.errors[k]);
            }
            showTip(error.status, desc.join('<br/>'), 0)
        }

    });
}


$(function () {
    //Enable iCheck plugin for checkboxes
    //iCheck for checkbox and radio inputs
    $('.mailbox-messages input[type="checkbox"]').iCheck({
        checkboxClass: 'icheckbox_flat-blue',
        radioClass: 'iradio_flat-blue'
    });

    //Enable check and uncheck all functionality
    $("body").on('click', '.checkbox-toggle', function () {
        var clicks = $(this).data('clicks');
        if (clicks) {
            //Uncheck all checkboxes
            $(".mailbox-messages input[type='checkbox']").iCheck("uncheck");
            $(".fa", this).removeClass("fa-check-square-o").addClass('fa-square-o');
        } else {
            //Check all checkboxes
            $(".mailbox-messages input[type='checkbox']").iCheck("check");
            $(".fa", this).removeClass("fa-square-o").addClass('fa-check-square-o');
        }
        $(this).data("clicks", !clicks);
    });

    $('.search-form-button').click(function () {
        var self = $(this);
        var form = self.parents('form');
        form.attr('action', form.data('action'));
        searchData(self);
    });


    $('<mate name="remoteImage" >').attr('checked', $('input[name="remote"]').prop('checked')).appendTo('head');
    $('<mate name="waterMark" >').attr('checked', $('input[name="remote"]').prop('checked')).appendTo('head');
    $('input[name="remote"]').change(function () {
        $('mate[name="remoteImage"]').attr('checked', this.checked);
    });

    $('input[name="needwatermark"]').change(function () {
        $('mate[name="waterMark"]').attr('checked', this.checked);
    });


    $('body').on('click', '.pagination li a', function (event) {
        event.preventDefault();
        var self = $(this);
        var form = self.parents('form');
        form.attr('action', self.attr('href'));
        searchData(self);
    });


    $('.btn-cropper').click(function () {
        $('#modal-cropper').modal();
    })

    $('.btn-cropper2').click(function () {
        $('#modal-cropper').modal();
        var file = location.origin + $('.picname').val();
        getImageObj(file);
    })
});


function searchData(self) {
    var form = self.parents('form');
    var ation = form.attr('action');
    var data = form.serializeArray();
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        type: "GET",
        url: ation,
        data: data,
        success: function (data) {
            $('.box-body').html(data);
        },
        error: function (error, data2, data3) {
            var desc = [];
            for (var k in error.responseJSON.errors) {
                desc.push(k + ':' + error.responseJSON.errors[k]);
            }
            showTip(error.status, desc.join('<br/>'), 0)
            $('body').trigger('delete', [0]);
        }
    });
}