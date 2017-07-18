@push('js')
@if ($id)
    <script src="/adminlte/plugins/jQueryUi/jquery-ui.min.js"></script>


    <script>

        $(document).ready(function () {

            loadImages();

            $("#images-list").sortable({
                update: function (event, ui) {
                    var data = $(this).sortable('serialize');
                    // POST to server using $.post or $.ajax
                    $.ajax({
                        data: data,
                        headers: {
                            'X-CSRF-TOKEN': '{{csrf_token()}}'
                        },
                        type: 'PUT',
                        url: '{!! URL::route($routePrefix."update", ["parent"=>$id, "id"=>0]) !!}'
                    });
                }

            });
            $("#images-list").disableSelection();


            $('#images-list').on('click', '.timeline-body a.btn-danger', function (event) {

                if (confirm('Удалить изображение?')) {
                    $.ajax({
                        url: $(this).attr('data-href'),
                        type: 'DELETE',  // user.destroy
                        headers: {
                            'X-CSRF-TOKEN': '{{csrf_token()}}'
                        },
                        success: function (result) {
                            loadImages();
                        }
                    });
                    return false;
                }
                else {
                    return false;
                }


            });


            $(document).on('change', '#fileupload', function () {

                var formData    = new FormData(),
                    fileUpload  = $('#fileupload')[0].files;

                $.each(fileUpload, function(index, value){
                    formData.append('image[]', value);
                });



                $.ajax({
                    url: '{!! URL::route($routePrefix."store", ["parent"=>$id]) !!}',
                    method: "post",
                    headers: {
                        'X-CSRF-TOKEN': $('form [name="_token"]').val()
                    },
                    data: formData,
                    processData: false,
                    contentType: false,       // The content type used when sending data to the server.
                    cache: false,             // To unable request pages to be cached
                    beforeSend: function(){
                        $("#myModal").modal({backdrop: false});
                    },
                    complete: function() {
                        $("#myModal").modal("hide");
                    },
                    success: function (data)   // A function to be called if request succeeds
                    {
                        if (data.state == 'error') {
                            alert(data.message);
                        }
                        else {
                            loadImages();
                        }
                    }
                });
            });

            function loadImages() {
                $('#images-list').load('{!! URL::route($routePrefix."index", ["parent"=>$id]) !!}');
            }
        });
    </script>
@endif
@endpush

@if ($id)
    <div class="modal fade" id="myModal" role="dialog">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-body">
                    <img src="{{ asset('/img/preloader.svg') }}" alt="loader">
                </div>
            </div>
        </div>
    </div>

    {!! BootForm::label('Изображения') !!}

    <div class="clearfix">
        <span class="btn btn-success fileinput-button">
            <i class="icon-plus icon-white"></i>
            <span>Выберите изображение...</span>
            <input id="fileupload" multiple type="file" name="image">
        </span>
    </div>


    <div id="images-list" class="images-list">

    </div>

    <div class="clearfix"></div>
    <p class="help-block">Для сортировки изображений, перетаскивайте их с помощью мыши</p>

@else
    <p class="help-block">Сохраните запись, чтобы добавить изображения</p>
@endif