<?php
/**
 * Created by PhpStorm.
 * User: adumontois
 * Date: 11/10/2016
 * Time: 10:11
 */

namespace App\Traits;
use OCFram\BackController;

/**
 * trait AppController
 *
 * Trait qui génère le menu et le contenu de la page en fonction de l'authentification du membre.
 *
 * @package App\Traits
 */
trait AppController {
	/**
	 * Génère le menu de la page courante. Doit être appelée à la fin de chaque contrôleur.
	 */
	public function run() {
		/**
		 * @var $this BackController
		 */
		$menu = '<li><a href="/">Accueil</a></li>';
		if ( $this->app()->user()->isAuthenticated() ) {
			$menu .= '<li><a href="/admin/">Admin (connecté)</a></li>
				<li><a href="/logout.html">Déconnexion</a></li>
				<li><a href="/admin/news-insert.html">Ajouter une news</a></li>';
		}
		else {
			$menu .= '<li>(non connecté)</li>
				<li><a href="/create-account.html">Inscription</a></li>
				<li><a href="/connect.html">Connexion</a></li>';
		}
		$this->page()->addVar('menu', $menu);
	}
}