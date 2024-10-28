<?php

namespace App\Http\Controllers;
use App\Services\SparqlService;
use App\Services\sparqlServiceUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class sparqlUpdateController extends Controller
{
    protected $sparqlService;
    protected $sparqlServiceUpdate;

    public function __construct(SparqlService $sparqlService, SparqlServiceUpdate $sparqlServiceUpdate)
    {
        $this->sparqlService = $sparqlService;
        $this->sparqlServiceUpdate = $sparqlServiceUpdate;
    }

    public function create()
    {
        return view('sparql.don.create');
    }

    public function store(Request $request)
    {
        // Valider les données de la requête
        $validatedData = $request->validate([
            'type_aliment' => 'required|string|max:255',
            'quantité' => 'required|integer|min:1',
            'date_don' => 'required|date',
            'date_permption' => 'required|date',
            'statut_don' => 'required|string|max:255',

        ]);

        // Construire la requête SPARQL pour créer un nouveau don
        $query = "
        PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
        PREFIX your_ontology: <http://www.semanticweb.org/user/ontologies/2024/8/untitled-ontology-8#>

        INSERT DATA {
            _:don a your_ontology:Don ;
                  your_ontology:type_aliment \"{$validatedData['type_aliment']}\" ;
                  your_ontology:quantité \"{$validatedData['quantité']}\" ;
                  your_ontology:date_don \"{$validatedData['date_don']}\" ;
                  your_ontology:date_permption \"{$validatedData['date_permption']}\" ;
                  your_ontology:statut_don \"{$validatedData['statut_don']}\" ;.

        }
        ";

        // Exécuter la requête SPARQL
        $this->sparqlServiceUpdate->update($query); // Changement ici pour appeler update()

        // Rediriger avec un message de succès
        return redirect()->route('don.search')->with('success', 'Le don a été créé avec succès.');
    }

    // sparqlUpdateController.php

    public function delete(Request $request)
    {
        // Valider les données de la requête
        $validator = Validator::make($request->all(), [
            'attribute' => 'required|string|max:255',
            'value' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                             ->withErrors($validator)
                             ->withInput();
        }

        // Extraire l'attribut et la valeur
        $attribute = $request->input('attribute');
        $value = $request->input('value');

        try {
            // Construire la requête SPARQL pour supprimer le don
            $query = "
            PREFIX your_ontology: <http://www.semanticweb.org/user/ontologies/2024/8/untitled-ontology-8#>

            DELETE {
                ?don a your_ontology:Don ;
                     your_ontology:{$attribute} \"$value\" .
            }
            WHERE {
                ?don a your_ontology:Don ;
                     your_ontology:{$attribute} \"$value\" .
            }
            ";

            // Exécuter la requête SPARQL
            $this->sparqlServiceUpdate->update($query); // Appeler la méthode pour exécuter la mise à jour

            // Rediriger avec un message de succès
            return redirect()->route('don.search')->with('success', 'Le don a été supprimé avec succès.');

        } catch (\Exception $e) {
            // En cas d'erreur, rediriger avec un message d'erreur
            return redirect()->back()->with('error', 'Erreur lors de la suppression du don : ' . $e->getMessage());
        }
    }
/////////
    // Method for showing the create form for products
    public function createProduct()
    {
        return view('sparql.produits.create');
    }

    // Method for storing a new product
    public function storeProduct(Request $request)
    {
        // Valider les données de la requête
        $validatedData = $request->validate([
            'product_name' => 'required|string|max:255',
            'product_type' => 'required|string|in:Produit_Alimentaire,Produit_Frais',
            'quantity' => 'required|integer|min:1',
            'expiration_date' => 'required|date',
            'category' => 'nullable|string|max:255',
        ]);
    
        // Construire la requête SPARQL avec les données validées
        $query = "
        PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
        PREFIX your_ontology: <http://www.semanticweb.org/user/ontologies/2024/8/untitled-ontology-8#>
    
        INSERT DATA {
            _:produit a your_ontology:{$validatedData['product_type']} ; 
                      your_ontology:nom_aliment \"{$validatedData['product_name']}\" ; 
                      your_ontology:quantité_aliment \"{$validatedData['quantity']}\"^^<http://www.w3.org/2001/XMLSchema#integer> ; 
                      your_ontology:date_permption \"{$validatedData['expiration_date']}\"^^<http://www.w3.org/2001/XMLSchema#date> ;
                      " . ($validatedData['category'] ? "your_ontology:catégorie_aliment \"{$validatedData['category']}\" ." : "") . "
        }
        ";
    
        try {
            // Exécuter la requête SPARQL
            $this->sparqlServiceUpdate->update($query);
    
            // Rediriger avec un message de succès
            return redirect()->route('produit.calender')->with('success', 'Le produit a été créé avec succès.');
        } catch (\Exception $e) {
            // En cas d'erreur, enregistrer l'erreur et rediriger avec un message d'erreur
            Log::error('Erreur SPARQL : ' . $e->getMessage());
            return redirect()->back()->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }
      
    
    
}
