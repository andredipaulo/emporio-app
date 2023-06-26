@extends('adminlte::page')

@section('title', getenv('APP_NAME') )

@section('content_header')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1>Tipos</h1>
                </div>
            </div>
        </div>
    </section>
@stop

@section('content')

    <style>
        .select2-container .select2-selection--single{
            height: calc(2.25rem + 2px);
        }
    </style>

    {{--Start--}}
    <div class="card border">
        <div class="card-body">
            <table id="tableType" class="table table-ordered table-hover">
                <thead>
                <tr>
                    <th>Código</th>
                    <th>Categoria</th>
                    <th>Tipo</th>
                    <th style="width: 60%">Descrição</th>
                    <th>
                        <div>
                            Ações
                        </div>
                    </th>
                </tr>
                </thead>
                <tbody>
                {{--Tabela--}}
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <button class="btn btn-sm btn-primary" role="button" onclick="showForm()">Adicionar</button>
                    </div>
                    <div class="col-6">
                        <nav id="paginator">
                            <ul class="pagination justify-content-end">
                                {{--Paginator--}}
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{--End--}}
    {{--Modal Form--}}
    <div class="modal" tabindex="-1" role="dialog" id="dlgType">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form class="form-horizontal" id="formType">
                    <div class="modal-header">
                        <h5>Novo(a)</h5>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <input type="hidden" id="id" class="form-control">

                            <div class="form-group col-12">
                                <label for="category_id">Categoria</label>
                                <select class="select2" id="category_id" name="category_id">
                                    <option></option>
                                </select>
                            </div>

                            <div class="form-group col-12">
                                <label for="name">Tipo</label>
                                <input type="text" class="form-control" name="name" id="name" placeholder="Tipo">
                            </div>

                            <div class="form-group col-12">
                                <label for="description">Descrição</label>
                                <textarea class="form-control" name="description" id="description" placeholder="Descrição" rows="5"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-sm btn-primary">Salvar</button>
                        <button type="submit" class="btn btn-sm btn-danger" data-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{--End Modal Form--}}
@stop

@section('css')

@stop

@section('js')
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(function(){
            loaderTypes(1);
            loaderCategories();
        })

        $(document).ready(function(){
            $('#category_id').select2({
                placeholder: 'Selecione uma categoria',
                width: '100%',

                "language": {
                    "noResults": function(){
                        return "Nenhum resultado encontrado.";
                        // return "Nenhum resultado encontrado.<a href='#' class='btn btn-success'>Use it anyway</a>";
                    }
                },
                escapeMarkup: function (markup) {
                    return markup;
                }
            });
        });

        function loaderTypes(pagina){
            $.get( '/api/types', {page: pagina}, function(resp) {
                montarTabela(resp);
                montarPaginator(resp);

                $('#paginator>ul>li>a').click( function (){
                    loaderTypes( $(this).attr('pagina'));
                });

            });
        };

        function loaderCategories(){
            $.getJSON( '/api/categories/listAll', function(category) {
                for(i=0; i<=category.length; i++){
                    option =
                        '<option value="' + category[i]?.id +'">'+ category[i]?.name + '</option>';

                    $('#category_id').append(option);
                }
                return option;
            });
        };

        function montarTabela(data){

            $("#tableType>tbody>tr").remove();

            for( i=0; i<data.data.length; i++){

                line = showLine(data.data[i]);
                $('#tableType>tbody').append(line);
            }
        };

        function showLine(p ){
            var line =
                "<tr>"+
                    "<td>"+ p.id    +"</td>" +
                    "<td>"+ p.category.name +"</td>" +
                    "<td>"+ p.name  +"</td>" +
                    "<td>"+ p.description  +"</td>" +
                    "<td>"+
                        '<button class="btn btn-sm btn-primary" onclick="edit(' + p.id +')"> Editar </button> ' +
                        '<button class="btn btn-sm btn-danger" onclick="remove(' + p.id +')"> Apagar </button> ' +
                    "</td>"+
                "</tr>";

            return line;
        }

        function montarPaginator(data){

            if(data.total > data.per_page) {

                $('#paginator>ul>li').remove();
                $('#paginator>ul').append(getItemAnterior(data));

                nLinks = (data.last_page < 5) ? data.last_page : 5;

                if ((data.current_page - 2) <= 1) {
                    inicio = 1;
                    fim = nLinks;
                } else if ((data.last_page - data.current_page) < 2) {
                    inicio = (data.last_page - nLinks) + 1;
                    fim = data.last_page;
                } else {
                    inicio = data.current_page - 2;
                    fim = data.current_page + 2;
                }

                for (i = inicio; i <= fim; i++) {
                    s = getItem(data, i);
                    $('#paginator>ul').append(s);
                }

                $('#paginator>ul').append(getItemProximo(data));
            }
        }

        function getItemProximo(data){
            i = data.current_page +1;
            if ( data.last_page == data.current_page )
                s = ' <li class="page-item disabled"> ';
            else
                s = ' <li class="page-item"> ';
            s += ' <a class="page-link"  '+' pagina="'+ i +'" href="#"> Próximo </a></li>';
            return s;
        }

        function getItemAnterior(data){
            i = data.current_page -1;
            if ( 1 == data.current_page )
                s = ' <li class="page-item disabled"> ';
            else
                s = ' <li class="page-item"> ';
            s += ' <a class="page-link"  '+' pagina="'+ i +'"href="#"> Anterior </a></li>';
            return s;
        }

        function getItem(data, i){
            if ( i == data.current_page )
                s = ' <li class="page-item active"> ';
            else
                s = ' <li class="page-item"> ';
            s += ' <a class="page-link" '+' pagina="'+ i +'" href="#">' + i +'</a></li>';
            return s;
        }

        function showForm(){
            $('#id').val('');
            $('#category_id').val('');
            $('#name').val('');
            $('#description').val('');

            $('#dlgType').modal('show');
        }

        $('#formType').submit( function (e){
            e.preventDefault();
            if ($('#id').val() != ""){
                update();
            }else{
                add();
            }
            $('#dlgType').modal('hide');
        });

        function add(){
            type = {
                category_id : $('#category_id').val(),
                name : $('#name').val(),
                description : $('#description').val(),
            };

            $.post('/api/types', type, function (data){
                type = JSON.parse(data);
                line = showLine(type);
                $('#tableType>tbody').append(line);
            });

        };

        function remove(id) {
            if (confirm('Realmente quer excluir?\nPressione Ok ou Cancelar.')) {
                $.ajax({
                    type: "DELETE",
                    url: "/api/types/" + id,
                    context: this,
                    success: function () {
                        lines = $("#tableType>tbody>tr");
                        e = lines.filter( function (i, element){
                            return element.cells[0].textContent == id;
                        })
                        if(e){
                            e.remove();
                        }
                    },
                    error: function (error) {

                    }
                });
            }
        };

        function edit(id) {
            $.getJSON('/api/types/' + id, function( data ){

                $('#id').val(data.id);
                $('#category_id').val(data.category_id);
                $('#category_id').trigger('change.select2');
                $('#name').val(data.name);
                $('#description').val(data.description);

                $('#dlgType').modal('show');
            });
        };

        function update()
        {
            type = {
                id : $('#id').val(),
                category_id : $('#category_id').val(),
                name : $('#name').val(),
                description : $('#description').val(),
            };

            $.ajax({
                type: "PUT",
                url: "/api/types/" + type.id,
                context: this,
                data: type,
                success: function (data) {

                    type = JSON.parse(data);

                    lines = $("#tableType>tbody>tr");

                    e = lines.filter( function (i, element){
                        return (element.cells[0].textContent == type.id);
                    });

                    if(e)
                    {
                        e[0].cells[0].textContent = type.id;
                        e[0].cells[1].textContent = type.category_id;
                        e[0].cells[2].textContent = type.name;
                        e[0].cells[3].textContent = type.description;
                    }
                },
                error: function (error) {
                    console.log(error);
                }
            });
        }
    </script>
@stop
