<?php
/**
 * Created by PhpStorm.
 * User: adumontois
 * Date: 03/10/2016
 * Time: 16:19
 */

/**
 * @var $News              \Entity\News
 * @var $Comment_list_a    \Entity\Comment[]
 * @var $User              OCFram\User
 * @var $action_a string[]
 */

?>

<p>
	Par <em><?= htmlspecialchars( $News[ 'User' ] ) ?></em>, le <?= $News[ 'dateadd' ] ?>
</p>
<h2><?= htmlspecialchars( $News[ 'title' ] ) ?></h2>
<p><?= nl2br( htmlspecialchars( $News[ 'content' ] ) ) ?></p>

<?php if ( $News[ 'dateadd' ] != $News[ 'dateupdate' ] ): ?>
	<p style="text-align: right;">
		<small><em>Modifiée le <?= $News[ 'dateupdate' ] ?></em></small>
	</p>
<?php endif; ?>

<p>
	<a href="commenter-<?= $News[ 'id' ] ?>.html">Ajouter un commentaire</a>
</p>

<?php if ( empty( $Comment_list_a ) ): ?>
	<p>
		Aucun commentaire n'a encore été posté. Soyez le premier à en laisser un !
	</p>
<?php endif; ?>

<?php foreach ( $Comment_list_a as $Comment ): ?>
	<fieldset>
		<legend>
			Posté par <strong><?= htmlspecialchars( $Comment[ 'User' ] ) ?></strong> le <?= $Comment[ 'date' ] ?>
			<?= $action_a[$Comment['id']] ?>
		</legend>
		<p>
			<?= nl2br( htmlspecialchars( $Comment[ 'content' ] ) ) ?>
		</p>
	</fieldset>
<?php endforeach; ?>

<p>
	<a href="commenter-<?= $News[ 'id' ] ?>.html">Ajouter un commentaire</a>
</p>