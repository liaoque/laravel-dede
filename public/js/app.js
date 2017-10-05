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


$('.modal .btn-primary').click(function () {
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
            data = JSON.parse(data);
            if (data.message == 'success') {
                self.parents('.modal').modal('toggle');
                showTip('成功', '移动成功', 1);
            }
        },
        error: function (error, data2, data3) {
            console.log(error, data2, data3)
            var desc = [];
            for (var k in error.responseJSON.errors) {
                desc.push(k + ':' + error.responseJSON.errors[k]);
            }
            showTip(error.status, desc.join('<br/>'), 0)
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




