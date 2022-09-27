$(document).ready(function() {
    /* Lorsque du clic sur un bouton d'ajout au panier */
    $(".btnAjax").each(function() {
        $(this).on({
            click: function() {
                // On récupère l'id, la qte et le prix unitaire de l'article
                let idArticle = $(this).data('idarticle');
                let qte = $('#qtePanier_' + idArticle).val();
                let prixU = $('#prixUArticle_' + idArticle).text() * 1;
                let montantArticle = qte * prixU;
                // Requête AJAX, en retour on affiche le msg reçu du controller et met à jour les montant de l'article et le montant total
                $.ajax({
                    type: "POST",
                    url: "modifQtePanier",
                    data: {idArticle: idArticle, qte: qte},
                    success: function (result) {
                        $('#divMsgPanier').addClass(result.etat);
                        $('#divMsgPanier').show();
                        $('#msgPanier').html(result.msg);

                        let montantTot = 0;
                        let qteTot = 0;
                        $("[name='qte']").each(function() {
                            qteTot += ($(this).val() * 1);
                        });

                        $('#montantTotArticle_' + idArticle).html(montantArticle.toFixed(2));

                        $(".montantTotalArticle").each(function() {
                            montantTot += ($(this).text() * 1);
                        });
                        if(montantTot > 0) {
                            $('#montantTotal').html(montantTot.toFixed(2));
                            $('#totalArticleMenu').html(qteTot.toString());
                        }
                   }
                });
            }
        });
    });

    $(".btnSuppressionAjax").each(function() {
        $(this).on({
            click: function() {
                // On récupère l'id, la qte et le prix unitaire de l'article
                let idArticle = $(this).data('idarticle');
                // Requête AJAX, en retour on affiche le msg reçu du controller, supprime la ligne de l'article et met à jour le montant total
                $.ajax({
                    type: "POST",
                    url: "supprLignePanier",
                    data: {idArticle: idArticle},
                    success: function (result) {
                        $('#divMsgPanier').addClass(result.etat);
                        $('#divMsgPanier').show();
                        $('#msgPanier').html(result.msg);

                        if (result.panier !== null) {
                            $('#lignePanier_' + idArticle).hide();
                            
                            let montantTot = 0;
                            let qteTot = 0;
                            $.each(result.panier, function(index, value) {                                
                                montantTot += (value.qtePanier * value.prixU);
                                qteTot += (value.qtePanier * 1);
                              });
                            if(montantTot > 0) {
                                $('#montantTotal').html(montantTot.toFixed(2));
                                $('#totalArticleMenu').html((qteTot * 1).toString());
                            }
                        } else {
                            $('#lignePanier_' + idArticle).hide();
                            $('#totalArticleMenu').html(0);
                            $('#ligneMontantPanier').html("<td colspan='5'>Votre panier est vide</td>");
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