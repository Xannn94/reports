<?php
namespace App\Modules\Admins\Models;

use App\Models\Model;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Notifications\Notifiable;



/**
 * App\Modules\Admins\Models\Admins
 *
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Model active()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Model admin()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Model filtered()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Admins\Models\Admins order()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Admins\Models\Admins sortable($defaultSortParameters = null)
 * @mixin \Eloquent
 */
class Admins extends Model
{
    use Notifiable, Sortable;

    public function scopeOrder($query){
        return $query;
    }
}
