//fonction pour récupérer l'host pour les répertoires

function myHost(host){
	var myhost = {
					"localhost.ma-cave-a-vin.fr":"www.ma-cave-a-vin.fr",
					"ma-cave-a-vin.fr":"www.ma-cave-a-vin.fr",
					"www.ma-cave-a-vin.fr":"www.ma-cave-a-vin.fr",
					"dedie.ma-cave-a-vin.fr":"www.ma-cave-a-vin.fr",
					"dediepro.ma-cave-a-vin.fr":"www.ma-cave-a-vin.fr",
					"pro.ma-cave-a-vin.fr":"www.ma-cave-a-vin.fr",
					"www.my-wine-cabinet.com":"www.ma-cave-a-vin.fr",
					"my-wine-cabinet.com":"www.ma-cave-a-vin.fr",
					"pro.my-wine-cabinet.com":"www.ma-cave-a-vin.fr",
					"dedie.my-wine-cabinet.com":"www.ma-cave-a-vin.fr",
					"dediepro.my-wine-cabinet.com":"www.ma-cave-a-vin.fr",
					"localhost.ma-cave-a-vin.fr":"www.ma-cave-a-vin.fr",
					"localhost.pro.ma-cave-a-vin.fr":"www.ma-cave-a-vin.fr",
					"localhost.my-wine-cabinet.com":"www.ma-cave-a-vin.fr",
					"localhost.pro.my-wine-cabinet.com":"www.ma-cave-a-vin.fr",
					"test.ma-cave-a-vin.fr":"www.ma-cave-a-vin.fr",
					"testpro.ma-cave-a-vin.fr":"www.ma-cave-a-vin.fr",
					"test.my-wine-cabinet.com":"www.ma-cave-a-vin.fr",
					"testpro.my-wine-cabinet.com":"www.ma-cave-a-vin.fr",
					"www.mon-espace-cuisson.com":"www.mon-espace-cuisson.com",
					"localhost.mon-espace-cuisson.com":"www.mon-espace-cuisson.com",
					"fusionmcav.ma-cave-a-vin.fr":"www.ma-cave-a-vin.fr"
				};

	return myhost[host]; 
}