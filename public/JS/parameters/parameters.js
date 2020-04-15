$.switcher('input[type=checkbox]');
// script de prévisualisation d'image
var openFile = function(event) {
    var input = event.target;

    var reader = new FileReader();
    reader.onload = function(){
        var dataURL = reader.result;
        var output = document.getElementById('image-preview');
        output.src = dataURL;
    };
    reader.readAsDataURL(input.files[0]);
};
$( function() {
    $( "#user" ).autocomplete({
        source : function( request, response ) {
            $.ajax( {
                url: "/Users_Json",
                dataType: "json",
                data: {
                    term: request.term
                },
                success : function( data ) {
                    response( data );
                }
            });
        },
        minLength: 3,
        autoFocus: true,
        focus: function( event, ui ) {
            $( "#user" ).val( ui.item.text );
            return false;
        },
        select: function( event, ui ) {
            $( "#user" ).val( ui.item.text );
            $( "#user-id" ).val( ui.item.id );
            $( "#user-icon" ).attr( "src", "/image-user/" + ui.item.icon );
            return false;
        }
    }).autocomplete( "instance" )._renderItem = function( ul, item ) {
        return $( "<li>" )
            .attr( "data-value", item.id )
            .append("<div><img src='/image-user/"+item.icon+"' style='height:25px;'>"+ item.text +"</div>")
            .appendTo( ul )
    }
});
function update(id){
    var td = $("#"+id);

    form = $("<form method='post'></form>");
    form.append('<input type="text" class="form-control" name="nameContentType" value="'+td.html()+'">')
        .append('<input type="hidden" name="idContentType" value='+id+'>')
        .append("<button type='submit' class='btn btn-info'><i class='fa fa-edit'></i></button>");

    td.html(form);
}