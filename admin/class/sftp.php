<?php
/**
 * Cette classe permet de publier des fichiers en SFTP. Pour le FTP classique, voir la classe "ftp.php".
 */
class sftp 
{
	private $server;
	private $user;
	private $pass;

	private $error = array();
	private $ftpid = false;

	/**
	 * Constructeur de la classe
	 *
	 * @param string $server Hôte FTP
	 * @param string $user   Login
	 * @param string $pass   Password
	 */
	public function __construct($server, $user, $pass)
	{
		try 
		{
			$this->server = $server;
			$this->user   = $user;
			$this->pass   = $pass;

			if (!function_exists("ssh2_connect")) throw new Exception('La librairie SSH2 n\'est pas installée sur le serveur');

			if($this->ftpid = ssh2_connect($this->server, 22))
		    {
		        if(!ssh2_auth_password($this->ftpid, $this->user, $this->pass)) throw new Exception('Erreur d\'authentification');
		    }
		    else
		    {
		    	throw new Exception('Connexion au serveur impossible');
		    }
		} 
		catch(Exception $e) 
		{
			$this->alerte_erreur($e);
		}
	}

	/**
	 * Permet de déposer un fichier sur l'espace SFTP
	 *
	 * @param  string $source      Chemin absolu vers le fichier local
	 * @param  string $destination Chemin vers le fichier distant
	 *
	 * @return boolean             Retourne "true" si la publication s'est passé comme prévu
	 */
	public function put($source,$destination)
	{
		try
		{
			if(!$this->ftpid) throw new Exception('Classe sftp non instanciée');

			if(!ssh2_scp_send($this->ftpid, $source, $destination, 0644))
			{
				throw new Exception('Erreur lors de la copie du fichier "' . $source . '"');
			}
			else
			{
				return true;
			}
		}
		catch(Exception $e)
		{
			$this->alerte_erreur($e); 
		}
	}

	/**
	 * Affiche une erreur survenue lors du traitement FTP
	 *
	 * @param  object $obj Objet contenant l'erreur
	 */
	private function alerte_erreur($obj)
	{
		echo "<p style=\"color: red;\">Erreur : " . $obj->getMessage() . "</p>";
	}
}