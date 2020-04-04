<!doctype html>
<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of plugin dcScript for Dotclear 2.
 * Copyright © 2014-2020 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/

	### Constants ###
	define('MCRYPT', 'mcrypt');
	define('OPENSSL', 'openssl');
	define('OPENSSL_METHOD', 'AES-256-CBC');

	function encrypt($str, $key, $cryptLib=OPENSSL) {
		//$key = pack('H*', hash('sha256', $key));
		$key = pack('H*', $key);
		if($cryptLib == MCRYPT) { // REMOVED in PHP 7.2
			if(version_compare(PHP_VERSION, '7.2', '>=')) { throw new Exception('Encryption incompatible with PHP 7.2 and more'); }
			$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
			return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $str, MCRYPT_MODE_ECB, $iv)));
		} elseif($cryptLib == OPENSSL) {
			$ivlen = openssl_cipher_iv_length(OPENSSL_METHOD);
			$iv = openssl_random_pseudo_bytes($ivlen);
			return trim(base64_encode($iv.openssl_encrypt($str, OPENSSL_METHOD, $key, OPENSSL_RAW_DATA, $iv)));
		} else {
			// unknown cryptLib
		}
	}

	function decrypt($str, $key, $cryptLib=MCRYPT) {
		//$key = pack('H*', hash('sha256', $key));
		$key = pack('H*', $key);
		if($cryptLib == MCRYPT) { // REMOVED in PHP 7.2
			if(version_compare(PHP_VERSION, '7.2', '>=')) { throw new Exception('Encryption incompatible with PHP 7.2 and more'); }
			$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
			return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, base64_decode($str), MCRYPT_MODE_ECB, $iv));
		} elseif($cryptLib == OPENSSL) {
			$ivlen = openssl_cipher_iv_length(OPENSSL_METHOD);
			$str = base64_decode($str);
			return trim(openssl_decrypt(substr($str, $ivlen), OPENSSL_METHOD, $key, OPENSSL_RAW_DATA, substr($str, 0, $ivlen)));
		} else {
			// unknown cryptLib
		}
	}

	$getKey = empty($_POST['key']) ? '' : $_POST['key'];
	$getCrypt = empty($_POST['crypte']) ? '' : $_POST['crypte'];
	$getDecrypte = (empty($_POST['key']) or empty($_POST['crypte'])) ? '' : decrypt($_POST['crypte'] ,$_POST['key'], MCRYPT);

	if(isset($_POST['download']) && $getDecrypte){
		$filename = '"download.txt"';
		header('Content-Disposition: attachment;filename='.$filename);
		header('Content-Type: text/plain; charset=UTF-8');
		echo $getDecrypte;
		exit;
	}

?>
<html>
	<head>
		<title>Décryptage mcrypt pour php inférieur à 7.2 / Mcrypt decryption for php less than 7.2</title>
		<meta charset="UTF-8">
		<style type="text/css">
			body { background-color: #FFFAFA; }
			h1 { text-align: center; }
			input[type="text"], textarea { width: 99.5%; }
			.right { text-align: right; }
			.copy_done { color: green;}
			.warning { color: #323232; background: #fefacd; border: 1px solid #ffd478; display: block; padding: 1em 1em .33em 1em; margin-bottom: 1em;}
		</style>
		<script type="text/javascript">

			function copy(element, button) {
				if (element) {
					var range = document.createRange();
					var selection = window.getSelection();
					range.selectNode(element);
					selection.removeAllRanges();
					selection.addRange(range);
					if (button) { button.classList.remove('copy_done'); }
					if (document.execCommand('copy')) {
						if (button) { button.classList.add('copy_done'); }
					} else {
						console.log('Erreur de copie de "' + element.id + '"');
					}
				}
			}

			document.addEventListener('DOMContentLoaded', function() {

				document.getElementById('copy_result').addEventListener('click', function() { copy(document.getElementById('decrypte'), this); });

				document.getElementById('clear').addEventListener('click', function(e) {
					document.getElementById('key').value = '';
					document.getElementById('crypte').value = '';
					document.getElementById('decrypte').innerHTML = '';
					e.stopPropagation();
					e.preventDefault();
				});
			});

		</script>
	</head>
	<body>
		<div class="right"><p><small>v1.2.0</small></p></div>
		<h1>Décryptage pour dcScript / Decryption for dcScript</h1>
		<?php
			if(version_compare(PHP_VERSION, '5.3.3', '<') || version_compare(PHP_VERSION, '7.2', '>=')) {
				echo '<div class="warning">
					<p><strong>ERREUR FATALE :</strong> La version PHP doit être comprise entre 5.3.3 <= PHP <7.2</p>
					<p><strong> FATAL ERROR: </strong> The PHP version must be between 5.3.3 <= PHP <7.2</p>
					</div>';
				exit;
			}
		?>
		<form action="#" method="post">
			<p class="right"><button type="button" id="clear">Vider / clean</button></p>
			<!-- Entrées -->
			<p>
				<label>Clé de cryptage / Encryption key : </label><br />
				<input id="key" name="key" type="text" placeholder="Coller la clé de cryptage ici / Paste the encryption key here" required="required" autocomplete="off" size ="133" value="<?php echo $getKey; ?>"/>
			</p>
			<p>
				<label>Element crypté / Encrypted element : </label><br />
				<input id="crypte" name="crypte" type="text" placeholder="Coller l'élément crypté ici / Paste the encrypted item here" required="required" autofocus="true" autocomplete="off" spellcheck="false" size ="133" value="<?php echo $getCrypt; ?>"/>
			</p>
			<!-- Validation -->
			<p class="right"><input type="submit" name="valid" value="Décrypter / Decrypt" /></p>
			<!-- Résultat -->
			<p>
				<label>Element décrypté / Decrypted element : </label><br />
				<textarea id="decrypte" name="decrypte" placeholder="Résultat du décryptage / Decryption result" rows="10" cols="100" spellcheck="true" wrap="off" readonly="true"><?php echo $getDecrypte; ?></textarea>
			</p>
			<!-- Download / copy -->
			<p class="right"><input type="submit" name="download" <?php echo ($getDecrypte ? '' : 'disabled="disabled"'); ?> value="Sauvegarder / Save" />&nbsp;<button type="button" id="copy_result">Copier / Copy</button></p>
		</form>
	</body>
</html>
