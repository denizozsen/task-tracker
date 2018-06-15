<?php

namespace App\Service;

use App\Exceptions\UserFacingException;
use App\Task;
use App\Mail\AccountVerification;
use App\Mail\Invite;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\User;
use App\Verification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Mail;

/**
 * Provides functionality for creating task reports.
 */
class ReportService
{
    /** @var UserRepository */
    private $userRepository;

    /** @var TaskRepository */
    private $taskRepository;

    public function __construct(UserRepository $userRepository, TaskRepository $taskRepository)
    {
        $this->userRepository = $userRepository;
        $this->taskRepository = $taskRepository;
    }

    /**
     * Generates a report with weekly values, based on all task records for the given user.\
     *
     * The returned array contains an entry for each week, starting with the week containing the earliest task
     * record and ending with the week containing the last task record.
     * Each entry in the returned array is an associative array that looks like the following:
     *
     * [
     *     'total'         => 140.00
     *     'average_daily' => 20.00
     * ]
     *
     * @throws UserFacingException if given user id is invalid
     */
    public function weekly(int $userId): array
    {
        // Note: we will throw an exception, while the implementation of this feature is still in progress.
        throw new UserFacingException('Reports are not implemented yet', 'not_implemented', 500);

        try {
            $this->userRepository->getById($userId);
        } catch(ModelNotFoundException $e) {
            throw new UserFacingException("Invalid user id: {$userId}");
        }

        // TODO - figure out how to enable calling query builder methods via repository
        $tasks = Task::query()->where(['user' => $userId])->orderBy('date_time', 'asc')->get();

        $firstDate = new Carbon($tasks->first()->date_time);
        $firstWeek = $firstDate->format("W");
        $lastDate = new Carbon($tasks->last()->date_time);
        $lastWeek = $lastDate->format("W");

        $report = [];
        for ($currentWeek = $firstWeek; $currentWeek <= $lastWeek; ++$currentWeek) {

        }

        return $report;
    }
}
