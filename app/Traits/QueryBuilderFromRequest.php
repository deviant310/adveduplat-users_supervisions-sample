<?php
namespace App\Traits;

use App\Http\Requests\QueryBuilderRequest;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

const FILTER_RELATIONS_CLAUSES = [
    'HAS' => 'has',
    'DOES_NOT_HAVE' => 'doesntHave',
    'OR_HAS' => 'orHas',
    'OR_DOES_NOT_HAVE' => 'orDoesntHave'
];

const FILTER_CLAUSES = [
    'EQUAL' => 'equal',
    'NOT_EQUAL' => 'not_equal',
    'MORE_THAN' => 'more_than',
    'LESS_THAN' => 'less_than',
    'IN' => 'in',
    'CONTAINS' => 'contains',
    'STARTS_WITH' => 'starts_with',
    'ENDS_WITH' => 'ends_with',
    'EMPTY' => 'empty',
    'NOT_EMPTY' => 'not_empty'
];

const FILTER_LOGIC_OPERATORS = [
    'AND' => 'and',
    'OR' => 'or'
];

trait QueryBuilderFromRequest
{
    private Builder $query;

    /**
     * Apply selection rules from request from query.
     *
     * @param Builder $query
     * @param QueryBuilderRequest $request
     * @return Builder
     */
    public function scopeBuildQueryFromRequest(Builder $query, QueryBuilderRequest $request): Builder
    {
        $this->query = $query;

        $rules = [
            'append' => function(string $key, $appends, Closure $message) {
                foreach ($appends as $append){
                    $method = Str::camel('get_' . $append . '_attribute');

                    if(!method_exists($this->query->getModel(), $method)) {
                        return $message(Lang::get('validation.db_attribute_exists', [
                            'attribute' => $append
                        ]));
                    }
                }

                return null;
            },
            'with' => function(string $key, $relations, Closure $message) {
                foreach ($relations as $relationRaw){
                    list($relationName) = explode(':', $relationRaw);

                    if(!method_exists($this->query->getModel(), $relationName)) {
                        return $message(Lang::get('validation.relation_exists', [
                            'relation' => $relationName,
                            'schema' => $this->query->getModel()->getTable()
                        ]));
                    }
                }

                return null;
            },
            'filters' => function(string $key, $filters, Closure $message){
                $model = $this->query->getModel();

                foreach ($filters as $index => $filter){
                    if(!is_array($filter))
                        return $message(Lang::get('validation.array', ['attribute' => "$key.*"]));

                    if(!isset($filter['column']) && !isset($filter['relation_clause']))
                        return $message(Lang::get('validation.required_without', [
                            'attribute' => "$key.$index.column",
                            'values' => 'relation_clause'
                        ]));

                    if(empty($filter['column'])  && !isset($filter['relation_clause']))
                        return $message(Lang::get('validation.filled', ['attribute' => "$key.$index.column"]));

                    if(isset($filter['clause'])){
                        if(!is_string($filter['clause']))
                            return $message(Lang::get('validation.string', ['attribute' => "$key.$index.clause"]));

                        if(!in_array($filter['clause'], array_values(FILTER_CLAUSES)))
                            return $message(Lang::get('validation.in_array', [
                                'attribute' => "$key.$index.clause",
                                'other' => implode('|', array_values(FILTER_CLAUSES))
                            ]));
                    }

                    if(
                        isset($filter['column']) &&
                        !isset($filter['value']) &&
                        !in_array($filter['clause'] ?? null, [FILTER_CLAUSES['EMPTY'], FILTER_CLAUSES['NOT_EMPTY']])
                    )
                        return $message(Lang::get('validation.required_with', [
                            'attribute' => "$key.$index.value",
                            'values' => 'column'
                        ]));

                    if(!empty($filter['logic'])){
                        if(!in_array($filter['logic'], array_values(FILTER_LOGIC_OPERATORS)))
                            return $message(Lang::get('validation.in_array', [
                                'attribute' => "$key.$index.logic",
                                'other' => implode('|', array_values(FILTER_LOGIC_OPERATORS))
                            ]));
                    }

                    if(empty($filter['relation']) && !empty($filter['relation_clause'])){
                        return $message(Lang::get('validation.required_with', [
                            'attribute' => "$key.$index.relation",
                            'values' => $filter['relation_clause']
                        ]));
                    }

                    if(!empty($filter['relation'])){
                        $relationName = $filter['relation'];

                        if(!method_exists($model, $relationName)) {
                            return $message(Lang::get('validation.relation_exists', [
                                'relation' => $relationName,
                                'schema' => $model->getTable()
                            ]));
                        }

                        $relatedModel = $model->$relationName()->getModel();

                        if(!empty($filter['column'])){
                            if(!in_array($filter['column'], $relatedModel->getFilterable() ?? [])){
                                return $message(Lang::get('validation.in_array', [
                                    'attribute' => $filter['column'],
                                    'other' => get_class($relatedModel) . '->filterable'
                                ]));
                            }
                        }
                    } else {
                        if(!empty($filter['column'])){
                            if(!in_array($filter['column'], $this->getFilterable() ?? [])){
                                return $message(Lang::get('validation.in_array', [
                                    'attribute' => $filter['column'],
                                    'other' => get_class($model) . '->filterable'
                                ]));
                            }
                        }
                    }

                    if(!empty($filter['relation_clause'])){
                        if(!in_array($filter['relation_clause'], array_values(FILTER_RELATIONS_CLAUSES)))
                            return $message(Lang::get('validation.in_array', [
                                'attribute' => "$key.$index.relation_clause",
                                'other' => implode('|', array_values(FILTER_RELATIONS_CLAUSES))
                            ]));
                    }
                }

                return null;
            },
        ];

        $attributes = $request->validate(collect($request->rules())
            ->reduce(function ($array, $value, $key) use ($rules){
                $array[$key] = collect($value)->merge($rules[$key] ?? null)->all();

                return $array;
            }, []));

        if(!empty($attributes['with']))
            $this->setQueryWith($attributes['with']);

        if(!empty($attributes['select']))
            $this->setQuerySelectedFields($attributes['select']);

        if(!empty($attributes['filters']))
            $this->setQueryWhere($attributes['filters']);

        $this->setQuerySort($attributes['sort'] ?? []);
        $this->setQueryPagination($attributes['page'] ?? 0, $attributes['limit'] ?? -1);

        return $this->query;
    }

    /**
     * Get the filterable attributes for the model.
     *
     * @return array
     */
    public function getFilterable(): array
    {
        return $this->filterable;
    }

    /**
     * Apply relations for query builder.
     *
     * @param array $relations
     */
    private function setQueryWith(array $relations = [])
    {
        $this->query->with($relations);
    }

    /**
     * Apply selected fields for query builder.
     *
     * @param array $fields
     */
    private function setQuerySelectedFields(array $fields = [])
    {
        $filteredFields = collect($fields)->filter()->all();

        $this->query->select(...$filteredFields);
    }

    /**
     * Apply filter for query builder.
     *
     * @param array $filters
     */
    private function setQueryWhere(array $filters = [])
    {
        foreach ($filters as $filter) {
            $relationClause = $filter['relation_clause'] ?? null;
            $relation = $filter['relation'] ?? null;
            $column = $filter['column'] ?? null;
            $clause = $filter['clause'] ?? null;
            $value = $filter['value'] ?? null;
            $logic = $filter['logic'] ?? null;

            if (!empty($relation)) {
                $relationWhereClause = $this->getRelationWhereClause($relationClause);

                $this->query->$relationWhereClause($relation, function (Builder $query) use ($column, $clause, $value, $logic) {
                    $relatedTable = $query->getModel()->getTable();
                    $relatedTableColumn = $column ? "$relatedTable.$column" : null;

                    if($relatedTableColumn){
                        list($whereClause, $whereParams) = $this->getWhereClauseWithParams($relatedTableColumn, $clause, $value, $logic);

                        $query->$whereClause(...$whereParams);
                    }
                });
            } else {
                list($whereClause, $whereParams) = $this->getWhereClauseWithParams($column, $clause, $value, $logic);

                $this->query->$whereClause(...$whereParams);
            }
        }
    }

    /**
     * Getting relation where clause.
     *
     * @param $rawClause
     * @return string
     */
    private function getRelationWhereClause($rawClause): string
    {
        switch ($rawClause) {
            case FILTER_RELATIONS_CLAUSES['HAS']: default:
                return 'whereHas';
            case FILTER_RELATIONS_CLAUSES['DOES_NOT_HAVE']:
                return 'whereDoesntHave';
            case FILTER_RELATIONS_CLAUSES['OR_HAS']:
                return 'orWhereHas';
            case FILTER_RELATIONS_CLAUSES['OR_DOES_NOT_HAVE']:
                return 'orWhereDoesntHave';
        }
    }

    /**
     * Getting where clause with query builder parameters.
     *
     * @param string|null $column
     * @param string|null $clause
     * @param null $value
     * @param string|null $logic
     * @return array
     */
    private function getWhereClauseWithParams(string $column, string $clause = null, $value = null, string $logic = null): array
    {
        if(empty($logic))
            $logic = 'and';

        switch ($clause){
            case FILTER_CLAUSES['EQUAL']: default:
                return ['where', [$column, '=', $value, $logic]];
            case FILTER_CLAUSES['NOT_EQUAL']:
                return ['where', [$column, '!=', $value, $logic]];
            case FILTER_CLAUSES['MORE_THAN']:
                return ['where', [$column, '>', $value, $logic]];
            case FILTER_CLAUSES['LESS_THAN']:
                return ['where', [$column, '<', $value, $logic]];
            case FILTER_CLAUSES['IN']:
                return ['whereIn', [$column, is_array($value) ? $value : [$value], $logic]];
            case FILTER_CLAUSES['CONTAINS']:
                return ['where', [$column, 'ilike', "%$value%", $logic]];
            case FILTER_CLAUSES['STARTS_WITH']:
                return ['where', [$column, 'ilike', "$value%", $logic]];
            case FILTER_CLAUSES['ENDS_WITH']:
                return ['where', [$column, 'ilike', "%$value", $logic]];
            case FILTER_CLAUSES['EMPTY']:
                return ['whereNull', [$column, $logic]];
            case FILTER_CLAUSES['NOT_EMPTY']:
                return ['whereNotNull', [$column, $logic]];
        }
    }

    /**
     * Apply filter for query builder.
     *
     * @param array $sort
     */
    private function setQuerySort(array $sort)
    {
        $model = $this->query->getModel();
        $table = $model->getTable();
        $column = $model->getKeyName();

        $this->query
            ->orderBy(
                $table . "." . ($sort['property'] ?? $column),
                $sort['direction'] ?? 'asc'
            );
    }

    /**
     * Apply filter for query builder.
     *
     * @param int $page
     * @param int $limit
     */
    private function setQueryPagination(int $page, int $limit)
    {
        if ($limit !== -1)
            $this->query->forPage($page, $limit);
    }
}
