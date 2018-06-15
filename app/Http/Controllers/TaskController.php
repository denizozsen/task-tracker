<?php

namespace App\Http\Controllers;

use App\Exceptions\UserFacingException;
use App\Task;
use App\Http\Filter\TaskFilter;
use App\Repository\TaskRepository;
use App\Service\ReportService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    /** @var TaskRepository */
    private $taskRepository;

    public function __construct(TaskRepository $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    /**
     * Creates a new Task.
     *
     * @throws UserFacingException
     */
    public function create(Request $request, int $userId): JsonResponse
    {
        $request->validate([
            'title'         => 'required|max:250',
            'starts_on'     => 'date',
            'interval_type' => 'required|in:day,week,month,year',
            'interval'      => 'int|min:1',
        ]);

        try {
            /** @var Task $task */
            $task = $this->taskRepository->createNew();
            $task->user          = $userId;
            $task->title         = $request->input('title');
            if ($request->has('description')) {
                $task->description = $request->input('description');
            }
            $task->starts_on = $request->has('starts_on') ? new Carbon($request->input('starts_on')) : DB::raw('NOW()');
            $task->interval_type = $request->input('interval_type');
            if ($request->has('interval')) {
                $task->interval = $request->input('interval');
            }
            $task->save();
            $savedTask = $this->taskRepository->getById($task->id);
            return $this->jsonResponse(['task' => $savedTask->toArray()]);
        } catch (\Throwable $e) {
            throw new UserFacingException("Failed to create task");
        }
    }

    /**
     * Retrieves all Tasks.
     */
    public function index(TaskFilter $filter, int $userId): JsonResponse
    {
        // TODO - create new repository method that returns query builder, so we can do e.g.
        //        $this->taskRepository->getWhere(['user' => $userId])->filter($filter)->get()->toArray()
        $tasks = Task::where(['user' => $userId])->filter($filter)->get()->toArray();
        return $this->jsonResponse(['tasks' => $tasks]);
    }

    /**
     * Retrieves the specified Task.
     *
     * @throws UserFacingException
     */
    public function get(int $userId, int $id): JsonResponse
    {
        try {
            $task = $this->taskRepository->getFirst(['user' => $userId, 'id' => $id]);
            return $this->jsonResponse(['task' => $task]);
        } catch (ModelNotFoundException $exception) {
            throw new UserFacingException("Task with id {$id} not found");
        }
    }

    /**
     * Updates the specified Task.
     *
     * @throws UserFacingException
     */
    public function update(Request $request, int $userId, int $id): JsonResponse
    {
        $request->validate([
            'title'         => 'max:250',
            'starts_on'     => 'date',
            'interval_type' => 'in:day,week,month,year',
            'interval'      => 'int|min:1',
        ]);

        try {
            /** @var Task $task */
            $task = $this->taskRepository->getFirst(['user' => $userId, 'id' => $id]);
            $task->title         = $request->input('title') ?? $task->title;
            $task->description   = $request->input('description') ?? $task->description;
            $task->starts_on     = $request->input('starts_on') ? new Carbon($request->input('starts_on')) : $task->starts_on;
            $task->interval_type = $request->input('interval_type') ?? $task->interval_type;
            $task->interval      = $request->input('interval') ?? $task->interval;
            $task->save();
            $savedTask = $this->taskRepository->getById($id);
            return $this->jsonResponse(['task' => $savedTask->toArray()]);
        } catch (\Throwable $e) {
            throw new UserFacingException("Failed to save task");
        }
    }

    /**
     * Deletes the specified Task.
     *
     * @throws UserFacingException
     */
    public function delete(int $userId, int $id): JsonResponse
    {
        try {
            $task = $this->taskRepository->getFirst(['user' => $userId, 'id' => $id]);
            $task->delete();
            return $this->jsonResponse(['id' => $id]);
        } catch (\Throwable $e) {
            throw new UserFacingException("Failed to delete task with id {$id}");
        }
    }

    /**
     * Creates weekly report over all task records for the given user
     *
     * @throws UserFacingException
     */
    public function report(ReportService $reportService, int $userId): JsonResponse
    {
        $report = $reportService->weekly($userId);
        return $this->jsonResponse(['report' => $report]);
    }
}
