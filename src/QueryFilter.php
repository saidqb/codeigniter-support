<?php

namespace Saidqb\CodeigniterSupport;

use Saidqb\CorePhp\Lib\Str;
use Saidqb\CorePhp\Pagination;

// With closure

class QueryFilter
{
    protected $queryBuilder = null;
    protected $tableSelectAs = [];
    protected $defaultRequest = [
        'page' => 1,
        'limit' => 10,
        'order_by' => 'DESC',
        'sort' => [],
        'search' => '',
    ];

    protected $query;
    protected $select;
    protected $search;
    protected $request;


    static function make()
    {
        return new static();
    }

    /* get data db $query = DB:table()*/
    /**
     * @param $request
     * @param $query
     * @param $setFilter
     */
    public function queryBuilder($request, $query, $setFilter)
    {
        $this->query = $query;
        $this->select = !isset($setFilter['select']) ? ['*'] : $setFilter['select'];
        $this->search = !isset($setFilter['search']) ? [] : $setFilter['search'];
        $this->request = $request;
    }

    public function query($query = null)
    {
        $this->query = $query;
        return $this;
    }

    public function search($search = [])
    {
        $this->search = $search;
        return $this;
    }

    public function select($select = [])
    {
        $this->select = $select;
        return $this;
    }

    public function request($request = [])
    {
        $this->request = $request;
        return $this;
    }

    public function get()
    {
        $pagination = Pagination::make();

        $query = $this->query;
        $req = $this->request;

        $defaultData = [
            'order_by' => ['asc', 'desc'],
        ];

        $defaultRequest = $this->defaultRequest;


        $req = array_merge($defaultRequest, $req);

        foreach ($req as $k => $v) {
            if (empty($v)) {
                if (isset($defaultRequest[$k])) {
                    $req[$k] = $defaultRequest[$k];
                }
            }
        }

        if (!is_numeric($req['limit'])) {
            $req['limit'] = $defaultRequest['limit'];
        }

        if ($req['limit'] == -1) {
            $req['limit'] = $pagination->getNoLimit();
            $pagination = $pagination->showAll();
        }

        $columns = [];
        foreach ($this->select as $key => $v) {
            $v = trim($v);
            if (strpos($v, ' as ') !== false) {
                $vArr = explode(' as ', $v);
                $v = trim($vArr[1]);
            }
            $columns[] = $v;
        };

        if (count($columns) == 1 && isset($columns[0]) && $columns[0] == '*') {
        } else {
            $this->tableSelectAs = $columns;
        }

        // $query->query('Where 1');

        if (!empty($this->tableSelectAs)) {
            if (empty($this->request)) {
            } else {
                $this->query = $query;
                $query = $this->filterQuery();
            }
        }

        if (!empty($this->search) && !empty($req['search'])) {
            $query->groupStart();
            foreach ($this->search as $key => $v) {
                $query->orWhere($v . ' LIKE', "%{$req['search']}%");
            }
            $query->groupEnd();
        }

        if (!empty($req['sort'])) {
            if (is_array($req['sort']) && !empty($req['sort'])) {
                foreach ($req['sort'] as $k => $v) {
                    if (in_array($k, $this->tableSelectAs) && in_array(strtolower($v), $defaultData['order_by'])) {
                        $query->orderBy("{$k} {$v}");
                    }
                }
            } else {
                if (Str::make($req['sort'])->startsWith('-')) {
                    $columnsSort = substr($req['sort'], 1);
                    if (in_array($columnsSort, $this->tableSelectAs)) {
                        $query->orderBy("{$columnsSort} desc");
                    }
                } else {
                    $columnsSort = $req['sort'];
                    if (in_array($columnsSort, $this->tableSelectAs)) {
                        $query->orderBy("{$req['sort']} asc");
                    }
                }
            }
        }

        $items = $query->limit(10)->select($this->select);

        // print_r($query->getResultArray());
        // die;
        $pagination = $pagination->totalItems($items->countAllResults(false))
            ->itemPerPage($req['limit'])
            ->currentPage($req['page']);


        $content['items'] = $items->get()->getResultArray();
        $content['pagination'] = $pagination->get();
        return $content;
    }

    /**
     * Filter Query
     */
    public function filterQuery()
    {
        $query = $this->query;
        $tableSelectAs = $this->tableSelectAs;

        foreach ($this->request as $field => $value) {
            if (in_array($field, $tableSelectAs)) {
                if (is_array($value)) {
                    foreach ($value as $comparison => $val) {
                        if ($val !== '') {
                            switch ($comparison) {
                                case 'eq':
                                    $query->where($field . ' =', $val);
                                    break;

                                case 'neq':
                                    $query->where($field . ' !=', $val);
                                    break;

                                case 'lt':
                                    $query->where($field . ' <', $val);
                                    break;

                                case 'gt':
                                    $query->where($field . ' >', $val);
                                    break;

                                case 'lte':
                                    $query->where($field . ' <=', $val);
                                    break;

                                case 'gte':
                                    $query->where($field . ' >=', $val);
                                    break;

                                case 'le':
                                    $query->where($field . ' like', "$val%");
                                    break;

                                case 'ls':
                                    $query->where($field . ' like', "%$val");
                                    break;

                                case 'lse':
                                    $query->where($field . ' like', "%$val%");
                                    break;

                                case 'in':
                                    $val = !is_array($val) ? explode(',', $val) : $val;
                                    $query->whereIn($field, $val);
                                    break;

                                case 'nin':
                                    $val = !is_array($val) ? explode(',', $val) : $val;
                                    $query->whereNotIn($field, $val);
                                    break;
                            }
                        }
                    }
                } else {
                    if ($value !== '') {
                        $query->where($field . ' =', $value);
                    }
                }
            }
        }
        return $query;
    }
}
