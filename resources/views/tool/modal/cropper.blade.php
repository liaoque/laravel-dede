<div class="modal fade bs-example-modal-lg in" id="modal-cropper" style="display: none">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
            </div>
            <form action="{{route('admin.uploader.stream')}}" method="post">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="exampleInputFile">浏览</label>
                        <input type="file" name="upfile" id="exampleInputFile">
                    </div>
                    <div class="row">
                        <div class="col-md-9">
                            <div class="avatar-wrapper" id="example-avatar-wrapper" style="height: 364px;"></div>
                        </div>
                        <div class="col-md-3">
                            <div class="avatar-preview preview-lg"></div>
                            <div class="avatar-preview preview-md"></div>
                            <div class="avatar-preview preview-sm"></div>
                            <img src="" id="asdasdasda">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary btn-file-uploader">确定</button>
                </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<style>
    .avatar-preview {
        margin-top: 15px;
        margin-right: 15px;
        border: 1px solid #eee;
        border-radius: 4px;
        background-color: #fff;
        overflow: hidden;
    }

    .preview-lg {
        height: 184px;
        width: 184px;
        margin-top: 15px;
    }

    .preview-md {
        height: 100px;
        width: 100px;
    }

    .preview-sm {
        height: 50px;
        width: 50px;
    }

</style>
@push('script2')
    <link href="/adminlte/plugins/bootstrop-cropper/cropper.min.css" rel="stylesheet">
    <script src="/adminlte/plugins/bootstrop-cropper/cropper.min.js"></script>

    <script>
        var img = $('<img>').appendTo('#example-avatar-wrapper'),
            file = oldSrc = active = false;
        $('#exampleInputFile').change(function () {
            var files = $(this).prop('files');

            if (files.length > 0) {
                file = files[0];
                if (oldSrc) {
                    URL.revokeObjectURL(oldSrc);
                }
                oldSrc = URL.createObjectURL(file)
                getImageObj(oldSrc)
            }
        });

        $('.btn-file-uploader').click(function () {
            var self = $(this);
            var form = self.parents('form');
            var ation = form.attr('action');
            var dataURL = img.cropper("getCroppedCanvas");
            var imgurl = dataURL.toDataURL("image/jpeg", 0.6);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                type: "POST",
                url: ation,
                data: {
                    upfile: imgurl
                },
                success: function (data) {
                    if (data.message == 'success') {
                        showTip('成功', '操作成功', 1);
                        self.parents('.modal').modal('toggle');
                        $('.picname').val(data.url);
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
        })

        function getImageObj(src) {
            if (img.active) {
                return img.cropper('replace', src);
            }
            img.active = true;
            img.attr('src', src);
            img.cropper({
                aspectRatio: 4 / 3,
                preview: $('.avatar-preview'),
                strict: true
            });
        }


    </script>

@endpush