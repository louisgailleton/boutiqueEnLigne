$(document).ready(function() {
    /* Lorsque du clic sur un bouton d'ajout au panier */
    $(".btnAjax").each(function() {
        $(this).on({
            click: function() {
                // On récupère l'id de l'article et la quantité
                let idArticle = $(this).data('idarticle');
                let qte = $('#qte_' + idArticle).val();
                // Requête AJAX, on affiche ensuite le message reçu en retour 
                $.ajax({
                    type: "POST",
                    url: "/commande/ajoutPanier",
                    data: {idArticle: idArticle, qte: qte},
                    success: function (result) {
                        $('#divMsgPanier').addClass(result.etat);
                        $('#divMsgPanier').show();
                        $('#msgPanier').html(result.msg);
                        
                        let qteTot = 0;
                        $.each(result.panier, function(index, value) {
                            qteTot += (value.qtePanier *1);
                          });
                        if(qteTot > 0) {
                            $('#totalArticleMenu').html((qteTot * 1).toString());
                        }
                   }
                });
            }
        });
    });

    /* Permet de masquer le message reçu en retour */
    $('#btnMsgPanier').on({
        click: function(){            
            $('#divMsgPanier').hide();
        }
    });
});