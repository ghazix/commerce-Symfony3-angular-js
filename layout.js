var layout = angular.module('layout',[])
    .config(function($interpolateProvider){
        $interpolateProvider.startSymbol('#').endSymbol('#');
    })
    .directive('fileModel', ['$parse', function ($parse) {
        return {
            restrict: 'A',


            link: function(scope, element, attrs) {
                var model = $parse(attrs.fileModel);
                var modelSetter = model.assign;

                element.bind('change', function(){
                    scope.$apply(function(){
                        modelSetter(scope, element[0].files[0]);
                    });


                });
            }
        };
    }]);

layout.controller('layoutController', function($scope,$http) {

    console.log('angular pret layout !!!');

    $scope.getPanier = function () {


        $http.post("http://localhost/commerce/web/app_dev.php/client/panier/getPanier")
            .then(function (response) {

                $scope.getPanierTab = [];
                for (var i = 0; i < response.data.panier.length; i++) {
                    $scope.getPanierTab.push({
                        idPanier : response.data.panier[i].idPanier,
                        idProduit : response.data.panier[i].idProduit,
                        prixProduit : response.data.panier[i].prixProduit,
                        unitPrice : response.data.panier[i].unitPrice,
                        qtePanier : response.data.panier[i].qtePanier,
                        dispoProduit : response.data.panier[i].dispo,
                        dispoClass : response.data.panier[i].dispoClass,
                        nomProduit : response.data.panier[i].nomProduit
                    });

                }

                $scope.totalPanier = response.data.prixTotal;
                $scope.nbrPanier = response.data.nbrPanier;
            });

    }

    setInterval(function(){$scope.getPanier();}, 1000);

    $scope.deletePanier = function(id){

        $http.post(Routing.generate('deletePanier', { id: id }))
            .then(function (response) {
                toastr.info('Produit supprimé du panier','Information',2000);
            });
    }

    $scope.addFavoris = function(id) {

        $http({
            url: Routing.generate('addFavoris', {'id':id}),
            method: 'POST',
            headers : {
                'Content-Type' : 'application/x-www-form-urlencoded; charset=UTF-8'
            }
        }).success(function (response) {});
    }

    $scope.addPanier = function(id) {
        console.log($scope.quantite);
        $http({
            url: Routing.generate('addPanier', {'id':id}),
            method: 'POST',
            data: {
                'qty': $scope.quantite
            },
            headers : {
                'Content-Type' : 'application/x-www-form-urlencoded; charset=UTF-8'
            }
        }).success(function (response) {
            toastr.success('Vous avez ajouté un produit dans le panier','Information',2000);
        });
    }

});
