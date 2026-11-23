<?php

namespace App\Controllers\admin\owasp;

use App\Controllers\BaseController;
use App\Models\Owasp\OwaspMenaceModel;
use App\Models\Owasp\OwaspTopModel;
use App\Models\Owasp\OwaspContreMesureModel;

class AdminContenuOwaspController extends BaseController
{
    protected OwaspMenaceModel $menace;
    protected OwaspTopModel $top;
    protected OwaspContreMesureModel $contreMesure;

    public function __construct()
    {
        $this->menace = model(OwaspMenaceModel::class);
        $this->top    = model(OwaspTopModel::class);
        $this->contreMesure = model(OwaspContreMesureModel::class);
    }

    public function index()
    {
        $data = ['tops' => $this->top->findAll()];
        return view('header') .
            view('admin/owasp/choixContenu', $data) .
            view('footer');
    }

    public function choixTop($id)
    {
        $top = $this->top->getTopById($id);
        if ($top === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'top' => $top,
            'menaces' => $this->menace->getMenaces($id),
        ];
        return view('header') .
            view('admin/owasp/tabOwaspContenu', $data) .
            view('footer');
    }

    public function modifElement(string $top, int $id)
    {
        $menace = $this->menace->getMenace($top, $id);
        if ($menace === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'menace' => $menace,
            'contreMesures' => $this->menace->getMenaceContreMesures($id),
            'topId' => $top,
            'menaceId' => $id,
        ];

        return view('header') .
            view('admin/owasp/modifElement', $data) .
            view('footer');
    }
    public function enregistrerMenace(string $top, int $id)
    {
        $url = 'floribondant/contenu/' . $top . '/modifier/' . $id;
        $data = [
            'nom_menace' => $this->request->getPost('nom_menace'),
            'desc_menace' => $this->request->getPost('desc_menace'),
            'prog_menace' => $this->request->getPost('prog_menace'),
        ];
        $enregistre = $this->menace->updateMenace($id, $data);
        if (!$enregistre) {
            return redirect()->to($url)->with('erreur', 'La menace n’a pas pu être enregistrée.')->withInput();
        }
        return redirect()->to($url)->with('succes', 'La menace a été enregistrée.');
    }
    public function ajouterContreMesure(string $top, int $id)
    {
        $url = 'floribondant/contenu/' . $top . '/modifier/' . $id;
        $data = [
            'desc_cm' => $this->request->getPost('nouvelle_desc_cm'),
            'prog_cm' => $this->request->getPost('nouveau_prog_cm'),
        ];
        $enregistre = $this->contreMesure->ajouterContreMesure($id, $data);
        if (! $enregistre) {
            return redirect()->to($url)->with('erreur', 'La contre-mesure n’a pas pu être ajoutée.')->with('ajout_cm_ouvert', true)->withInput();
        }
        return redirect()->to($url)->with('succes', 'La contre-mesure a été ajoutée.');
    }

    public function enregistrerContreMesure(string $top, int $id, int $cmId)
    {
        $url = 'floribondant/contenu/' . $top . '/modifier/' . $id;
        $data = [
            'desc_cm' => $this->request->getPost('desc_cm'),
            'prog_cm' => $this->request->getPost('prog_cm'),
        ];
        $enregistre = $this->contreMesure->updateContreMesure($id, $cmId, $data);
        if (!$enregistre) {
            return redirect()->to($url)->with('erreur', 'La contre-mesure n’a pas pu être enregistrée.');
        }
        return redirect()->to($url)->with('succes', 'La contre-mesure a été enregistrée.');
    }

    public function supprimerContreMesure(string $top, int $id, int $cmId)
    {
        $url = 'floribondant/contenu/' . $top . '/modifier/' . $id;
        if (! $this->contreMesure->supprimerContreMesure($id, $cmId)) {
            return redirect()->to($url)->with('erreur', 'La contre-mesure n’a pas pu être supprimée.');
        }

        return redirect()->to($url)->with('succes', 'La contre-mesure a été supprimée.');
    }
}