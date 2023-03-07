<?php

// Nom du fichier json pour le stockage des données
define('__DATABASE_FILENAME__', __DIR__ . '/books.json');

// Classe pour la gestion de la bibliothèque
class Library
{
    protected $_data = null;

    // Constructeur qui charge le fichier ou le crée si il n'exite pas
    public function __construct( )
    {
        if ( !file_exists(__DATABASE_FILENAME__) )
        {
            touch(__DATABASE_FILENAME__);
        }
        if ( ($content = file_get_contents(__DATABASE_FILENAME__)) !== false && ($json = json_decode($content, true)) !== null )
        {
            $this->_data = $json;
        }
        else
        {
            $this->_data = array();
        }
    }

    // Enregistrement des modifications dans le fichier
    private function save()
    {
        file_put_contents(__DATABASE_FILENAME__, json_encode($this->_data, JSON_PRETTY_PRINT));
    }

    // Récupération d'un ou de tous les livres
    public function get( $id = null)
    {
        if ( $id === null )
        {
            return $this->_data;
        }
        elseif( preg_match('/^[0-9]+$/', $id) )
        {
            foreach ( $this->_data as $val )
            {
                if ( $val['id'] == $id )
                {
                    return $val;
                }
            }
        }
        return null;
    }

    // Creation d'un nouveau livre
    public function create( $data )
    {
        $next_id = 0;
        foreach ( $this->_data as $val )
        {
            if ( $val['id'] > $next_id )
            {
                $next_id = $val['id'];
            }
        }
        $data['id'] = $next_id + 1;
        $this->_data[] = $data;
        $this->save();
        return true;
    }

    // Mise à jour des informations pour un livre donné
    public function update( $id, $data )
    {
        foreach ( $this->_data as $key => $val )
        {
            if ( $val['id'] == $id )
            {
                $val = array_merge($val, $data);
                $this->_data[$key] = $val;
                $this->save();
                return true;
            }
        }
        return false;
    }

    // Suppression d'un livre donné
    public function delete( $id )
    {
        foreach ( $this->_data as $key => $val )
        {
            if ( $val['id'] == $id )
            {
                unset($this->_data[$key]);
                $this->save();
                return true;
            }
        }
        return false;
    }
}

// Fonction qui gère l'affichage du résultat du l'API en json
function display( $code, $data = false )
{
    $status = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported');

    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
    header("Last-Modified: " . gmdate("D, d M Y H:i:s" ) . " GMT" );
    header("Cache-Control: no-cache, must-revalidate" );
    header("Pragma: no-cache" );
    header("Access-Control-Allow-Orgin: *");
    header("Access-Control-Allow-Methods: *");
    header("Content-Type:application/json");
    header("HTTP/1.1 ".$code." ".$status[$code]);
    if ( $data !== false )
    {
        echo json_encode($data);
    }
    die();
}

// Test du chemin de la requête
if ( !isset($_GET['_uri']) )
{
    display(404, "Page non trouvée");
}
$uri = explode('/', trim($_GET['_uri'],'/'));
$service = array_shift($uri);

if ( $service != 'books' )
{
    display(503, "Service non displonible");
}

$lib = new Library();

switch ( $_SERVER['REQUEST_METHOD'] )
{
    case "GET":
        $data = $lib->get($uri[0] ?? null);
        if ( $data === null )
        {
            display(404);
        }
        else
        {
            display(200, $data);
        }
        break;
    case "POST":
        $params = file_get_contents("php://input");
        $data = json_decode($params, true);
        if ( $data === null )
        {
            display(204);
        }
        $lib->create($data);
        display(201);
        break;
    case "PUT":
        if ( !isset($uri[0]) )
        {
            display(405);
        }
        if ( !preg_match('/^[0-9]+$/', $uri[0]) )
        {
            display(404);
        }
        $params = file_get_contents("php://input");
        $data = json_decode($params, true);
        if ( $data === null )
        {
            display(204);
        }
        if ( $lib->update($uri[0], $data) )
        {
var_dump($data); die('Line:' . __LINE__);
            display(201);
        }
        else
        {
            display(204);
        }
        break;
    case "DELETE":
        if ( !isset($uri[0]) )
        {
            display(405);
        }
        if ( !preg_match('/^[0-9]+$/', $uri[0]) )
        {
            display(404);
        }
        if ( $lib->delete($uri[0]) )
        {
            display(200);
        }
        else
        {
            display(404);
        }
        break;
    default:
        display(501);
}