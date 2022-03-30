<?php

namespace Database\Factories;

use App\Courses;
use App\User;
use App\UserSupervision;
use App\UserSupervisionStatus;
use Exception;

class UserSupervisionFactory extends RelationFactory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserSupervision::class;

    /**
     * Define the model's default state.
     *
     * @return array
     * @throws Exception
     */
    public function definition(): array
    {
        list($userId, $courseId) = $this->getUniqueRelation(
            (new $this->model)
                ->select(['user_id', 'course_id'])
                ->get()
                ->map(fn($item) => [$item['user_id'], $item['course_id']]),
            User::select('id')->pluck('id'),
            Courses::select('id')->pluck('id')
        );

        if(empty($userId) || empty($courseId))
            throw new Exception("Cannot create factory" . __CLASS__ . ". No unique pair was provided.");

        return [
            'user_id' => $userId,
            'course_id' => $courseId,
            'status_id' => UserSupervisionStatus::all()->random()->id,
            'comment' => $this->faker->sentence,
            'deadline_at' => $this->faker->dateTime()->format('c'),
        ];
    }
}
