<?php
/**
 * Created by PhpStorm.
 * User: adumontois
 * Date: 04/10/2016
 * Time: 09:56
 */

namespace App\Backend\Modules\News;

use Entity\News;
use OCFram\BackController;
use OCFram\HTTPRequest;

class NewsController extends BackController
{
    public function executeIndex(HTTPRequest $request)
    // Récupère toutes les news disponibles en DB
    {
        $manager = $this -> managers -> getManagerOf('News');
        $this -> page -> addVar('title', 'Liste des news');
        $this -> page -> addVar('listeNews', $manager -> getList());
        $this -> page -> addVar('nbNews', $manager -> count());
    }

    public function processForm(HTTPRequest $request)
    // Gère l'insert ou l'update d'une news
    {
        $news = new News(array('auteur' => $request -> postData('auteur'),
                                'titre' => $request -> postData('titre'),
                                'contenu' => $request -> postData('contenu')));
        // S'il s'agit d'un update, il faut connaître l'id de la news qui est donné dans l'url
        if ($request -> postExists('id'))
        {
            $news -> setId($request -> postData('id'));
        }
        if (!$news -> isValid())
        {
            $this -> page -> addVar('erreurs', $news -> erreurs());
            // On ajoute un message au user pour lui dire que la news a bien été ajoutée/modifiée
            if ($news -> object_new())
            {
                $this -> app -> user() -> setFlash('La news a été correctement ajoutée');
            }
            else
            {
                $this -> app -> user() -> setFlash('La news a été correctement modifiée');
            }
        }
        else
        {
            $manager = $this -> managers -> getManagerOf('News');
            $manager -> save($news);
        }
        $this -> page -> addVar('news', $news);
    }

    public function executeInsert(HTTPRequest $request)
    // insère une news dans la DB
    {
        if ($request -> postExists('auteur'))
        {
            $this -> processForm($request);
        }
        $this -> page -> addVar('title', 'Insertion d\'une news');
    }

    public function executeUpdate(HTTPRequest $request)
    {
        if ($request -> postExists('auteur'))
        {
            $this -> processForm($request);
        }
        else
        {
            // Aller récupérer la news en DB
            $manager = $this -> managers -> getManagerOf('News');
            $news = $manager -> getUnique($request -> getData('id'));
            $this -> page -> addVar('news', $news);
        }
        $this -> page -> addVar('title', 'Modification d\'une news');
    }

    public function executeDelete(HTTPRequest $request)
    {
        if (!isset($request -> getExists('id')))
        {
            throw new \RuntimeException('Undefined news to delete');
        }
        $manager = $this -> managers -> getManagerOf('News');
        $news = $manager -> getUnique($request -> getData('id'));
        $manager -> delete($news);
        $this -> page -> addVar('title', 'Suppression d\'une news');
        $this -> app -> user() -> setFlash('La news '.$request -> getData('id').' a été correctement supprimée');
        $this -> app -> httpResponse() -> redirect('.');
    }
}