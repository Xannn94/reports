<?php

namespace App\Modules\Reports\Http\Controllers\Admin;

use App\Modules\Admin\Http\Controllers\Admin;
use App\Modules\Reports\Models\Reports;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Facades\Route;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Excel2007 as XLSX;
use PhpParser\ErrorHandler\Collecting;
use Storage;


class IndexController extends Admin
{
    /* тут должен быть slug модуля для правильной работы меню */
    public $page = 'reports';
    /* тут должен быть slug группы для правильной работы меню */
    public $pageGroup = 'modules';

    public $optimism;
    public $pessimism;
    public $total;

    public function getModel()
    {
        return new Reports();
    }

    public function getRules($request, $id = false)
    {
        return [];
    }

    public function index()
    {
        $request = Route::getCurrentRequest();

        if (!$request->has('filter')) {
            $tickets = collect();
            return view($this->getIndexViewName(), ['entities' => $tickets]);
        }

        $redmineId = Auth::guard('admin')->user()->redmine_id;
        $from = null;
        $to   = null;

        switch ($request->filter) {
            case 'last':
                $from = Carbon::today()->year . '-' . (Carbon::today()->month - 1) . '-1 00:00:01';
                $to = Carbon::today()->year . '-' . Carbon::today()->month . '-1 00:00:01';
                break;
            case 'current':
                $from = Carbon::today()->year . '-' . Carbon::today()->month . '-1 00:00:01';
                $to = Carbon::today()->year . '-' . (Carbon::today()->month + 1) . '-1 00:00:01';
                break;
        }

        $tickets = Reports::where('updated', '>', $from)
            ->where('updated', '<', $to)
            ->where('user_id', $redmineId)
            ->get();

        return view($this->getIndexViewName(), [
            'entities' => $tickets,
            'filter' => $request->filter
        ]);
    }

    public function show($action)
    {
        switch ($action) {
            case 'refresh':
                if ($this->_refresh()) {
                    return redirect()->back()->with(['message' => 'Ваши тикеты успешно обновлены']);
                } else {
                    return redirect()->back()->with(['message' => 'У вас нет ни одного тикета в Редмайне']);
                }
                break;
            case 'download':
                $this->_download();
                break;
            case 'send':
                break;
        }

        return redirect()->back()->with(['message' => 'Что-то пошло не так']);

    }

    private function _refresh()
    {
        $user = Auth::guard('admin')->user();
        $name = $user->name;
        $redmineId = $user->redmineId;

        $json = json_encode([
            'Login' => $name,
        ]);
        $ch = curl_init('http://report.web2.weltkind.ru/refresh');

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json;charset=\"utf-8\"',
            'Content-Length: ' . strlen($json)
        ]);

        if (!$result = curl_exec($ch)) {
            trigger_error(curl_error($ch));
        }

        curl_close($ch);

        $response = json_decode($result);
        if ($response->status == 'success') {
            //Удаляем из нашей базы(не из редмайн) все тикеты текущего пользователся
            Reports::where('user_id', $redmineId)->delete();

            foreach ($response->tickets as $ticket) {
                $reports = new Reports();

                $reports->user_id = $ticket->user_id;
                $reports->ticket_id = $ticket->ticket_id;
                $reports->project = $ticket->project;
                $reports->project_slug = $ticket->project_slug;
                $reports->ticket = $ticket->ticket;
                $reports->status = $ticket->status;
                $reports->updated = $ticket->updated;
                $reports->optimism = $ticket->optimism;
                $reports->pessimism = $ticket->pessimism;;
                $reports->total = $ticket->total;

                $reports->save();
            }
            return 1;
        } else {
            return 0;
        }

        /*$tickets = DB::connection('redmine')
            ->table('issues')
            ->select(
                'issues.id',
                'issues.updated_on',
                'projects.name',
                'projects.identifier as project_slug',
                'issues.subject',
                'issue_statuses.name as status'
            )
            ->join('projects', 'projects.id', '=', 'issues.project_id')
            ->join('issue_statuses', 'issue_statuses.id', '=', 'issues.status_id')
            ->where('assigned_to_id', $user->id)
            ->where('status_id', '!=', '5')
            ->orderBy('project_id', 'asc')
            ->orderBy('subject', 'asc')
            ->get();

        $ticketsId = $tickets->map(function ($item) {
            return $item->id;
        });

        $this->getOptimisms($ticketsId);
        $this->getPessimisms($ticketsId);
        $this->getTotals($ticketsId);

        if (!$tickets->isEmpty()) {
            foreach ($tickets as $ticket) {
                $reports = new Reports();

                $reports->user_id = $user->id;
                $reports->ticket_id = $ticket->id;
                $reports->project = $ticket->name;
                $reports->project_slug = $ticket->project_slug;
                $reports->ticket = $ticket->subject;
                $reports->status = $ticket->status;
                $reports->updated = $ticket->updated_on;
                if (isset($this->optimism[$ticket->id])) {
                    $reports->optimism = $this->optimism[$ticket->id];
                } else {
                    $reports->optimism = 0;
                }

                if (isset($this->pessimism[$ticket->id])) {
                    $reports->pessimism = $this->pessimism[$ticket->id];
                } else {
                    $reports->pessimism = 0;
                }

                if (isset($this->total[$ticket->id])) {
                    $reports->total = $this->total[$ticket->id];
                } else {
                    $reports->total = 0;
                }

                $reports->save();
            }

            return 1;
        } else {
            return 0;
        }*/
    }

    private function _download()
    {
        $filter = Route::getFacadeRoot()->getCurrentRequest()->query->get('filter');
        $user = Auth::guard('admin')->user();
        $name = $user->name;
        $from = null;
        $to = null;
        $monthTitle = null;
        $monthNumber = null;
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $months = [
            '1' => 'Январь',
            '2' => 'Февраль',
            '3' => 'Март',
            '4' => 'Апрель',
            '5' => 'Май',
            '6' => 'Июнь',
            '7' => 'Июль',
            '8' => 'Август',
            '9' => 'Сентябрь',
            '10' => 'Октябрь',
            '11' => 'Ноябрь',
            '12' => 'Декабрь',
        ];

        if (!$filter) {
            return redirect()->back()->with(['message' => 'Что-то пошло не так']);
        }

        switch ($filter) {
            case 'last':
                $from = Carbon::today()->year . '-' . (Carbon::today()->month - 1) . '-1 00:00:01';
                $to = Carbon::today()->year . '-' . Carbon::today()->month . '-1 00:00:01';
                $monthTitle = $months[Carbon::today()->month - 1];
                $monthNumber = Carbon::today()->month - 1;
                break;
            case 'current':
                $from = Carbon::today()->year . '-' . Carbon::today()->month . '-1 00:00:01';
                $to = Carbon::today()->year . '-' . (Carbon::today()->month + 1) . '-1 00:00:01';
                $monthTitle = $months[Carbon::today()->month];
                $monthNumber = Carbon::today()->month;
                break;
        }


        $tickets = Reports::where('updated', '>', $from)
            ->where('updated', '<', $to)
            ->where('user_id', $user->redmine_id)
            ->orderBy('updated', 'desc')
            ->get();

        $this->_setTitles($sheet);

        /* Заполняем ячейки данными */
        for ($i = 0; $i < $tickets->count(); $i++) {
            $line = 2 + $i;

            $sheet->setCellValue(('A' . $line), date('d.m.Y', strtotime($tickets[$i]->updated)));

            $sheet->setCellValue(('B' . $line), $tickets[$i]->project);
            $sheet->getCell(('B' . $line))->getHyperlink()->setUrl('http://redmine.web2.weltkind.ru/projects/' . $tickets[$i]->project_slug);

            $sheet->setCellValue(('C' . $line), $tickets[$i]->ticket);
            $sheet->getCell(('C' . $line))->getHyperlink()->setUrl('http://redmine.web2.weltkind.ru/issues/' . $tickets[$i]->ticket_id);

            $sheet->setCellValue(('D' . $line), $tickets[$i]->status);
            $sheet->setCellValue(('E' . $line), $tickets[$i]->optimism);
            $sheet->setCellValue(('F' . $line), $tickets[$i]->pessimism);
            $sheet->setCellValue(('G' . $line), $tickets[$i]->total);

            //Считаем только тикеты которые уже сделаны, т.е стоит статус "Решена"
            if ($tickets[$i]->status == 'Решена') {
                //Если тикет выполнен быстрее чем оценка оптимизм
                if ($tickets[$i]->optimism > $tickets[$i]->total) {
                    $sheet->setCellValue(('H' . $line), 1.5);
                } //Если тикет выполнен дольше чем оценка пессимизм
                elseif ($tickets[$i]->pessimism < $tickets[$i]->total) {
                    $sheet->setCellValue(('H' . $line), 0.5);
                } //Если время выполнения тикета находится между оценками оптимизм и пессимизм
                else {
                    $sheet->setCellValue(('H' . $line), 1);
                }
            } else {
                $sheet->setCellValue(('H' . $line), 0);
            }

            $sheet->setCellValue(('I' . $line), '=H' . $line . '*G' . $line);

            if ($tickets[$i]->comment) {
                $sheet->setCellValue(('J' . $line), $tickets[$i]->comment);
            }
        }

        /* Считаем сумму затраченых часов */
        $this->_setSpentHours($sheet, $tickets->count());

        /* Считаем сумму часов после умножения на коэфициент */
        $this->_setBonusHours($sheet, $tickets->count());

        /* Проставляем количество рабочих дней */
        $this->_setWorkDayCount(
            $sheet,
            $tickets,
            widget('quantity.' . Carbon::create(2017, $monthNumber, 1)->format('M') . '.' . Carbon::today()->year)
        );

        /* Подсчитываем сколько часов должны были отработать в идеале */
        $this->_setWorkHoursCount($sheet, $tickets);

        /* Вычисляем рабочий коефициент */
        $this->_setWorkСoef($sheet, $tickets->count());

        /* Вычисляем Коэфициент эффективности: */
        $this->_setСoef($sheet, $tickets->count());

        /* Вычисляем Номинальную премию */
        $this->_bonus($sheet, $tickets->count());

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Отчёт за ' . $monthTitle . '-' . Carbon::today()->year . '.xlsx';
        $path = $user->login . '/' . $fileName;
        $writer->save(public_path('uploads/excel') . '/' . $path);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        die();

    }

    private function _setTitles(Worksheet $sheet)
    {
        $sheet->setCellValue('A1', 'Дата тикета');
        $sheet->setCellValue('B1', 'Проект');
        $sheet->setCellValue('C1', 'Тикет');
        $sheet->setCellValue('D1', 'Текущий статус тикета');
        $sheet->setCellValue('E1', 'Оценка оптимизм');
        $sheet->setCellValue('F1', 'Оценка пессимизм');
        $sheet->setCellValue('G1', 'Всего затрачено');
        $sheet->setCellValue('H1', 'Коэффициент эффективности');
        $sheet->setCellValue('I1', 'Часы');
        $sheet->setCellValue('J1', 'Комментарий');

        /* Выставляем ширину колонок */
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('G')->setAutoSize(true);
        $sheet->getColumnDimension('H')->setAutoSize(true);
        $sheet->getColumnDimension('I')->setAutoSize(true);
        $sheet->getColumnDimension('J')->setAutoSize(true);

        /* Делаем шрифт жирным */
        $sheet->getStyle('A1')->getFont()->setBold('true');
        $sheet->getStyle('B1')->getFont()->setBold('true');
        $sheet->getStyle('C1')->getFont()->setBold('true');
        $sheet->getStyle('D1')->getFont()->setBold('true');
        $sheet->getStyle('E1')->getFont()->setBold('true');
        $sheet->getStyle('F1')->getFont()->setBold('true');
        $sheet->getStyle('G1')->getFont()->setBold('true');
        $sheet->getStyle('H1')->getFont()->setBold('true');
        $sheet->getStyle('I1')->getFont()->setBold('true');
        $sheet->getStyle('J1')->getFont()->setBold('true');
    }

    private function _setSpentHours($sheet, $count)
    {
        $sheet->getStyle('G' . ($count + 3))->getFont()->setBold('true');
        $sheet->setCellValueExplicit(
            'G' . ($count + 3),
            '=SUM(G2:G' . ($count + 1) . ')',
            \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_FORMULA
        );
    }

    private function _setBonusHours($sheet, $count)
    {
        $sheet->getStyle('I' . ($count + 3))->getFont()->setBold('true');
        $sheet->setCellValueExplicit(
            'I' . ($count + 3),
            '=SUM(I2:I' . ($count + 1) . ')',
            \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_FORMULA
        );
    }

    private function _setWorkDayCount($sheet, $tickets, $value)
    {
        $line = 6 + $tickets->count();
        $sheet->setCellValue('C' . $line, 'Рабочих дней');
        $sheet->setCellValue('D' . $line, $value);
    }

    private function _setWorkHoursCount($sheet, $tickets)
    {
        $line = 7 + $tickets->count();
        $sheet->setCellValue('C' . $line, 'Всего часов в месяце ( раб. Дня)');
        $sheet->setCellValue('D' . $line, '=D' . ($line - 1) . '*8');
    }

    private function _setWorkСoef($sheet, $count)
    {
        $line = 8 + $count;
        $lineTotal = 3 + $count;

        $sheet->setCellValue('C' . $line, 'Рабочий коэфициент:');
        $sheet->setCellValue('D' . $line, '=G' . $lineTotal . '/D' . ($line - 1));
    }

    private function _setСoef($sheet, $count)
    {
        $line = 9 + $count;
        $lineTotal = 3 + $count;

        $sheet->setCellValue('C' . $line, 'Коэфициент эффективности:');
        $sheet->setCellValue('D' . $line, '=I' . $lineTotal . '/G' . $lineTotal);
    }

    private function _bonus($sheet, $count)
    {
        $line = 11 + $count;
        $line2 = 12 + $count;
        $lineWorkCoef = 8 + $count;
        $lineCoef = 9 + $count;
        $lineTotal = 3 + $count;
        $user = Auth::guard('admin')->user();

        $sheet->setCellValue('C' . $line2, 'Номинальная премия');
        $sheet->setCellValue('D' . $line, '=D' . $lineWorkCoef . '*D' . $lineCoef);
        $sheet->setCellValue('D' . $line2, $user->premium);
        $sheet->setCellValue('D' . ($line2 + 1), '=D' . $line2 . '*D' . $line);
    }

    public function test()
    {
        $json = json_encode([
                'Login' => 'Alex_khan',
        ]);
        $ch = curl_init('http://report.web2.weltkind.ru/refresh');

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json;charset=\"utf-8\"',
            'Content-Length: ' . strlen($json)
        ]);

        if (!$result = curl_exec($ch)) {
            trigger_error(curl_error($ch));
        }

        curl_close($ch);

        $response = json_decode($result);
        if ($response->status == 'success') {
            foreach ($response->tickets as $ticket) {
                $reports = new Reports();

                $reports->user_id = $ticket->user_id;
                $reports->ticket_id = $ticket->ticket_id;
                $reports->project = $ticket->project;
                $reports->project_slug = $ticket->project_slug;
                $reports->ticket = $ticket->ticket;
                $reports->status = $ticket->status;
                $reports->updated = $ticket->updated;
                $reports->optimism = $ticket->optimism;
                $reports->pessimism = $ticket->pessimism;;
                $reports->total = $ticket->total;

                $reports->save();
            }
            return 1;
        } else {
            return 0;
        }
    }
}
