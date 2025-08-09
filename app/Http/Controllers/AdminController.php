<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    /**
     * Inscription d'un administrateur (un seul autorisé)
     */
    public function register(Request $request)
    {
        // Vérifie s'il existe déjà un admin
        if (User::count() > 0) {
            return response()->json(['message' => 'Un administrateur existe déjà'], 403);
        }

        // Validation des données reçues
        $validator = Validator::make($request->all(), [
            'nom'       => 'required|string|max:255',
            'prenom'    => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'telephone' => 'required|string|max:20',
            'sexe'      => 'required|string|in:masculin,feminin,autre',
            'password'  => 'required|string|min:6|confirmed',
            'photo'     => 'nullable|image|mimes:jpeg,jpg,png,gif|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Gestion du téléchargement de la photo
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('photos', 'public');
        }

        // Création de l'administrateur
        $user = User::create([
            'nom'       => $request->nom,
            'prenom'    => $request->prenom,
            'email'     => $request->email,
            'telephone' => $request->telephone,
            'sexe'      => $request->sexe,
            'password'  => Hash::make($request->password),
            'photo'     => $photoPath,
        ]);

        // Création du token d'authentification
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Administrateur créé avec succès',
            'user'    => $user,
            'token'   => $token,
        ], 201);
    }

    /**
     * Connexion de l'administrateur
     */
    public function login(Request $request)
    {
        // Validation simple des champs
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // Recherche de l'utilisateur par email
        $user = User::where('email', $request->email)->first();

        // Vérification mot de passe
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Identifiants incorrects'], 401);
        }

        // Génération du token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie',
            'user'    => $user,
            'token'   => $token,
        ]);
    }

    /**
     * Récupérer les infos du profil admin connecté
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Mise à jour du profil admin
     */
    public function update(Request $request)
    {
        $user = $request->user();

        // Validation des données
        $validator = Validator::make($request->all(), [
            'nom'       => 'sometimes|required|string|max:255',
            'prenom'    => 'sometimes|required|string|max:255',
            'email'     => 'sometimes|required|email|unique:users,email,' . $user->id,
            'telephone' => 'sometimes|required|string|max:20',
            'sexe'      => 'sometimes|required|string|in:masculin,feminin,autre',
            'password'  => 'nullable|string|min:6|confirmed',
            'photo'     => 'nullable|image|mimes:jpeg,jpg,png,gif|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Gestion de la nouvelle photo (suppression de l'ancienne si existante)
        if ($request->hasFile('photo')) {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            $user->photo = $request->file('photo')->store('photos', 'public');
        }

        // Mise à jour des champs simples
        foreach (['nom', 'prenom', 'email', 'telephone', 'sexe'] as $field) {
            if ($request->has($field)) {
                $user->$field = $request->$field;
            }
        }

        // Mise à jour du mot de passe si renseigné
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'message' => 'Profil mis à jour avec succès',
            'user'    => $user,
        ]);
    }

    /**
     * Déconnexion (suppression du token courant)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnexion réussie']);
    }

    /**
     * Suppression du compte admin
     */
    public function deleteSelf(Request $request)
    {
        $user = $request->user();

        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
        }

        $user->delete();

        return response()->json(['message' => 'Compte administrateur supprimé']);
    }
}
