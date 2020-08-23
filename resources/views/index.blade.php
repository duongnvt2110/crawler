<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">
        <link rel="stylesheet" href="https://unpkg.com/bootstrap-table@1.17.1/dist/bootstrap-table.min.css"><link rel="stylesheet" href="https://unpkg.com/bootstrap-table@1.17.1/dist/bootstrap-table.min.css">
        <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
        <script src="https://unpkg.com/bootstrap-table@1.17.1/dist/bootstrap-table.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

        <meta name="csrf-token" content="{{ csrf_token() }}">
        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Nunito', sans-serif;
                font-weight: 200;
                height: 100vh;
                margin: 0;
            }
            .form-button{
                text-align: center;
                margin: 10px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="form-group" style="padding-top: 30px;">
                <div>
                    <textarea name="patt" class="patt form-control" style="color: black;height:200px;"></textarea>
                </div>
                <div class="form-button">
                    <select class="form-control" id="select-crawl">
                        <option value=1>Test Url</option>
                        <option value=2>Get Url</option>
                        <option value=3>Get Info</option>
                    </select>
                </div>
                <div class="form-button url-form" style="display:none;">
                    <button type="button" class="btn-set-patt-all-url btn btn-secondary" style="display: inline-block;">Set Pattern All Url</button>
                    <button type="button" class="btn-show-pattern-url btn btn-info" style="display: inline-block;">Show Pattern Url</button>
                    <button type="button" class="btn-get-all-url btn btn-primary" style="display: inline-block;">Get All Url</button>
                </div>
                <div class="form-button info-form" style="display:none;">
                    <button type="button" class="btn-set-patt-info-product btn btn-secondary" style="display: inline-block;">Set Pattern Info</button>
                    <button type="button" class="btn-show-pattern-info btn btn-info" style="display: inline-block;">Show Pattern Info</button>
                    <button type="button" class="btn-get-info btn btn-primary" style="display: inline-block;">Get Info</button>
                </div>
                <div class="form-button test-form">
                    <button type="button" class="btn-test btn btn-secondary" style="display: inline-block;">Test</button>
                </div>
                <div class="form-button">
                    <button type="button" class="btn-remove-pattern btn btn-danger" style="display: inline-block;">Remove Pattern</button>
                </div>
                <form action="{{route('exportData')}}" method='GET' style="text-align: center;margin: 10px;">
                    <button type="submit" class="btn-export-data btn btn-success" type="submit" class="display: flex;">Export Excel</button>
                </form>
            </div>
            <div class="modal fade progressModal form-group" id="progress-modal" role="dialog" aria-labelledby="progressModal" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Progress Bar</h5>
                        </div>
                        <div class="modal-body">
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <br>
        <div>
            <div class="data-table">
            </div>
        </div>
    </body>

    <script>
        $(document).ready(function(){
            var data = '';
            $(".btn-get-all-url").click(function(){
                $('.progressModal').modal({backdrop: 'static', keyboard: false});
                const extractFunc = function(key){
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: "{{route('getAllUrl')}}",
                        data: {key:key},
                        type: 'POST',
                        success: function(result){
                            if(result.value == 100 || result.currentKey > result.maxKey){
                                $('.progress-bar').attr('aria-valuenow', result.value).css('width', result.value+'%').text(result.value);
                                setTimeout(function(){
                                    $('.progressModal').modal('hide');
                                }, 1000);
                                $('.progress-bar').attr('aria-valuenow', 0).css('width', 0+'%').text(0);
                                return;
                            }else{
                                $('.progress-bar').attr('aria-valuenow', result.value).css('width', result.value+'%').text(result.value);
                                extractFunc(result.currentKey);
                            }
                        }
                    });
                };
                extractFunc(0);
            });
            $(".btn-test").click(function(){
                let patt = $('.patt').val();
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{route('test')}}",
                    type: 'POST',
                    async: true,
                    data: {patt:patt},
                    success: function(result){
                        let table = '';
                        data = result;
                        if(result){
                            Object.keys(result).forEach(function(key){
                                table += '<td>';
                                table += '<table class="table">';
                                table += '<thead>';
                                table += '<tr><th scope="col">'+key+'</th></tr>';
                                table += '</thead>';
                                table += '<tbody>';
                                Object.keys(result[key]).forEach(function(index){
                                    table += '<tr><th>'+result[key][index]+'</th></tr>';
                                });
                                table += '</tbody>';
                                table += '</table>';
                                table +='</td>';
                            });
                            $(".data-table").html(table);
                        }

                    }
                });
            });
            $(".btn-get-info").click(function(){
                $('.progressModal').modal({backdrop: 'static', keyboard: false});
                const extractFunc = function(key){
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: "{{route('getInfo')}}",
                        data: {key:key},
                        type: 'POST',
                        success: function(result){
                            if(result.value == 100 || result.currentKey > result.maxKey ){
                                $('.progress-bar').attr('aria-valuenow', result.value).css('width', result.value+'%').text(result.value);
                                setTimeout(function(){
                                    $('.progressModal').modal('hide');
                                }, 1000);
                                $('.progress-bar').attr('aria-valuenow', 0).css('width', 0+'%').text(0);
                                return;
                            }else{
                                $('.progress-bar').attr('aria-valuenow', result.value).css('width', result.value+'%').text(result.value);
                                extractFunc(result.currentKey);
                            }
                        }
                    });
                };
                extractFunc(0);
            });
            // set Pattern Get All Url
            $(".btn-set-patt-all-url").click(function(){
                let patt = $('.patt').val();
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{route('setPattUrl')}}",
                    type: 'POST',
                    async: true,
                    data: {patt:patt},
                    success: function(result,status,xhr){

                    }
                });
            });
            // set Pattern Get Info Prodcut
            $(".btn-set-patt-info-product").click(function(){
                let patt = $('.patt').val();
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{route('setPattInfoProduct')}}",
                    type: 'POST',
                    async: true,
                    data: {patt:patt},
                    success: function(result){
                    }
                });
            });
            // set Pattern Get Info Prodcut
            $(".btn-show-pattern-url").click(function(){
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{route('showPattUrl')}}",
                    type: 'POST',
                    async: true,
                    success: function(result){
                        $('.patt').val(result);
                    }
                });
            });

            $(".btn-show-pattern-info").click(function(){
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{route('showPattInfo')}}",
                    type: 'POST',
                    async: true,
                    success: function(result){
                        $('.patt').val(result);
                    }
                });
            });

            $(".btn-show-pattern-info").click(function(){
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{route('showPattInfo')}}",
                    type: 'POST',
                    async: true,
                    success: function(result){
                        $('.patt').val(result);
                    }
                });
            });

            $(".btn-remove-pattern").click(function(){
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{route('deletePattern')}}",
                    type: 'POST',
                    async: true,
                    success: function(result){
                        $('.patt').val(result);
                    }
                });
            });

            $("#select-crawl").change(function(){
                value = $(this).val();
                $('.patt').val('');
                if(value == 1){
                    $('.test-form').show();
                    $('.info-form').hide();
                    $('.url-form').hide();
                }else if(value == 2){
                    $('.test-form').hide();
                    $('.url-form').show();
                    $('.info-form').hide();
                }else if (value == 3){
                    $('.test-form').hide();
                    $('.url-form').hide();
                    $('.info-form').show();
                }else{
                    $('.test-form').show();
                    $('.info-form').hide();
                    $('.url-form').hide();
                }
            });

        });
    </script>
</html>
