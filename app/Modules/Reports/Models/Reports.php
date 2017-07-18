<?php
namespace App\Modules\Reports\Models;

use App\Models\Model;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Notifications\Notifiable;


/**
 * App\Modules\Reports\Models\Reports
 *
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Model active()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Model admin()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Model filtered()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Reports\Models\Reports order()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Reports\Models\Reports sortable($defaultSortParameters = null)
 * @mixin \Eloquent
 */
class Reports extends Model
{
    use Notifiable, Sortable;

    public $fillable = [
        'user_id',
        'ticket_id',
        'project',
        'ticket',
        'status',
        'optimism',
        'pessimism',
        'total',
        'updated_on',
        'comment'
    ];

    public $timestamps = false;

    public function scopeOrder($query){

        return $query;
    }
}
