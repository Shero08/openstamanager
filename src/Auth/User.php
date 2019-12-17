<?php

namespace Auth;

use Common\Model;
use Intervention\Image\ImageManagerStatic;
use Models\Note;
use Models\Upload;
use Modules\Anagrafiche\Anagrafica;

class User extends Model
{
    protected $table = 'zz_users';

    protected $appends = [
        'is_admin',
        'gruppo',
        'id_anagrafica',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    protected $is_admin;
    protected $gruppo;

    /**
     * The attributes that should be d-none for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Crea un nuovo utente.
     *
     * @param Group  $gruppo
     * @param string $username
     * @param string $email
     * @param string $password
     *
     * @return self
     */
    public static function build(Group $gruppo, $username, $email, $password)
    {
        $model = parent::build();

        $model->group()->associate($gruppo);

        $model->username = $username;
        $model->email = $email;
        $model->password = $password;

        $model->enabled = 1;

        $model->save();

        return $model;
    }

    public function getIsAdminAttribute()
    {
        if (!isset($this->is_admin)) {
            $this->is_admin = $this->getGruppoAttribute() == 'Amministratori';
        }

        return $this->is_admin;
    }

    public function getIdAnagraficaAttribute()
    {
        return $this->attributes['idanagrafica'];
    }

    public function setIdAnagraficaAttribute($value)
    {
        $this->attributes['idanagrafica'] = $value;
    }

    public function getGruppoAttribute()
    {
        if (!isset($this->gruppo)) {
            $this->gruppo = $this->group->nome;
        }

        return $this->gruppo;
    }

    public function getSediAttribute()
    {
        $database = database();

        // Estraggo le sedi dell'utente loggato
        $sedi = $database->fetchArray('SELECT idsede FROM zz_user_sedi WHERE id_user='.prepare($this->id));

        // Se l'utente non ha sedi, è come se ce le avesse tutte disponibili per retrocompatibilità
        if (empty($sedi)) {
            $sedi = $database->fetchArray('SELECT "0" AS idsede UNION SELECT id AS idsede FROM an_sedi WHERE idanagrafica='.prepare($this->idanagrafica));
        }

        return array_column($sedi, 'idsede');
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = \Auth::hashPassword($value);
    }

    public function isAdmin()
    {
        return $this->is_admin;
    }

    public function getPhotoAttribute()
    {
        if (empty($this->image_file_id)) {
            return null;
        }

        $image = Upload::find($this->image_file_id);

        return ROOTDIR.'/'.$image->filepath;
    }

    public function setPhotoAttribute($value)
    {
        $module = module('Utenti e permessi');

        $data = [
            'id_module' => $module->id,
            'id_record' => $this->id,
        ];

        // Foto precedenti
        $old_photo = Upload::where($data)->get();

        // Informazioni sull'immagine
        $filepath = is_array($value) ? $value['tmp_name'] : $value;
        $info = Upload::getInfo(is_array($value) ? $value['name'] : $value);
        $file = DOCROOT.'/files/temp_photo.'.$info['extension'];

        // Ridimensionamento
        $driver = extension_loaded('gd') ? 'gd' : 'imagick';
        ImageManagerStatic::configure(['driver' => $driver]);

        $img = ImageManagerStatic::make($filepath)->resize(100, 100, function ($constraint) {
            $constraint->aspectRatio();
        });
        $img->save(slashes($file));

        // Aggiunta nuova foto
        $upload = Upload::build($file, $data);

        // Rimozione foto precedenti
        delete($file);
        if (!empty($upload)) {
            foreach ($old_photo as $old) {
                $old->delete();
            }
        }

        $this->image_file_id = $upload->id;
    }

    public function getNomeCompletoAttribute()
    {
        $anagrafica = $this->anagrafica;
        if (empty($anagrafica)) {
            return $this->username;
        }

        return $anagrafica->ragione_sociale.' ('.$this->username.')';
    }

    /* Relazioni Eloquent */

    public function group()
    {
        return $this->belongsTo(Group::class, 'idgruppo');
    }

    public function logs()
    {
        return $this->hasMany(Log::class, 'id_utente');
    }

    public function notes()
    {
        return $this->hasMany(Note::class, 'id_utente');
    }

    public function anagrafica()
    {
        return $this->belongsTo(Anagrafica::class, 'idanagrafica');
    }

    public function image()
    {
        return $this->belongsTo(Upload::class, 'image_file_id');
    }

    public function modules()
    {
        return $this->group->modules();
    }
}
