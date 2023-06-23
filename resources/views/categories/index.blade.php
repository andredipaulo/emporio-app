@extends('adminlte::page')

@section('title', getenv('APP_NAME') )

@section('content_header')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1>Categorias</h1>
                </div>
            </div>
        </div>
    </section>
@stop

@section('content')
    {{--Start--}}
    <div class="card border">
        <div class="card-body">
            <table id="tableCategory" class="table table-ordered table-hover">
                <thead>
                <tr>
                    <th>Código</th>
                    <th>Nome</th>
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
    <div class="modal" tabindex="-1" role="dialog" id="dlgCategory">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form class="form-horizontal" id="formCategory">
                    <div class="modal-header">
                        <h5>Nova Categoria</h5>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <input type="hidden" id="id" class="form-control">

                            <div class="form-group col-12">
                                <label for="name">Nome</label>
                                <input type="text" class="form-control" name="name" id="name" placeholder="Categoria">
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
            loaderCategory(1);
        })

        function loaderCategory(pagina){
            $.get( '/api/categories', {page: pagina}, function(resp) {
                // console.log(resp);
                montarTabela(resp);
                montarPaginator(resp);

                $('#paginator>ul>li>a').click( function (){
                    loaderCategory( $(this).attr('pagina'));
                });

            });
        };

        function montarTabela(data){

            $("#tableCategory>tbody>tr").remove();

            for( i=0; i<data.data.length; i++){
                line = showLine(data.data[i]);
                $('#tableCategory>tbody').append(line);
            }
        };

        function showLine(p){
            var line =
                "<tr>"+
                "<td>"+ p.id    +"</td>" +
                "<td>"+ p.name  +"</td>" +
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
            $('#name').val('');
            $('#dlgCategory').modal('show');
        }

        $('#formCategory').submit( function (e){
            e.preventDefault();
            if ($('#id').val() != ""){
                update();
            }else{
                add();
            }
            $('#dlgCategory').modal('hide');
        });

        function add(){
            category = {
                name : $('#name').val(),
            };

            $.post('/api/categories', category, function (data){
                category = JSON.parse(data);
                line = showLine(category);
                $('#tableCategory>tbody').append(line);
            });

        };

        function remove(id) {
            if (confirm('Realmente quer excluir?\nPressione Ok ou Cancelar.')) {
                $.ajax({
                    type: "DELETE",
                    url: "/api/categories/" + id,
                    context: this,
                    success: function () {
                        lines = $("#tableCategory>tbody>tr");
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
            $.getJSON('/api/categories/' + id, function( data ){
                $('#id').val(data.id);
                $('#name').val(data.name);

                $('#dlgCategory').modal('show');
            });
        };

        function update(){
            category = {
                id : $('#id').val(),
                name : $('#name').val(),
            };

            $.ajax({
                type: "PUT",
                url: "/api/categories/" + category.id,
                context: this,
                data: category,
                success: function (data) {

                    category = JSON.parse(data);

                    lines = $("#tableCategory>tbody>tr");

                    e = lines.filter( function (i, element){
                        return (element.cells[0].textContent == category.id);
                    });

                    if(e){
                        e[0].cells[0].textContent = category.id;
                        e[0].cells[1].textContent = category.name;
                    }
                },
                error: function (error) {
                    console.log(error);
                }
            });
        }
    </script>
@stop
