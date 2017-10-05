<script>
    var navListArrayOne = JSON.parse('{!! json_encode($navListArrayOne) !!}');
</script>

<div class="modal fade" id="modal-moving" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title" style="font-size: 14px">移动目录时不会删除原来已创建的列表，移动后需重新对栏目创建HTML。</h4>
            </div>
            <form action="{{route('admin.catalog.move')}}">
                <div class="modal-body">
                    <input type="hidden" name="id" value=""/>
                    <div class="form-group">
                        <label class="control-label">你选择的栏目是：</label>
                        <p class="form-control" id="nav"></p>
                    </div>
                    <div class="form-group">
                        <label for="message-text" class="control-label">你希望移动到那个栏目？</label>
                        .<select class="form-control movetype" name="movetype">


                        </select>
                    </div>
                    <div class="form-group">
                        <label for="message-text" class="control-label">注意事项：</label>
                        <p class="form-control text-red">不允许从父级移动到子级目录，只允许子级到更高级或同级或不同父级的情况。</p>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">确定</button>
                </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>