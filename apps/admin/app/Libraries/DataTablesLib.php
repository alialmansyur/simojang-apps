<?php

namespace App\Libraries;

class DataTablesLib
{
    protected $request;

    public function __construct()
    {
        $this->request = service('request');
    }

    public function render($builder, array $columns){
        $request = $this->request;
        $draw    = $request->getPost('draw');
        $start   = $request->getPost('start');
        $length  = $request->getPost('length');
        $search  = $request->getPost('search')['value'] ?? null;
        $order   = $request->getPost('order')[0] ?? null;
        $requestColumns = $request->getPost('columns') ?? [];
        $definitions = $this->normalizeColumns($columns);
        $totalRecords = $builder->countAllResults(false);

        if ($search) {
            $builder->groupStart();
            foreach ($definitions as $definition) {
                $searchTargets = $definition['search'];
                if ($searchTargets === false || $searchTargets === null) {
                    continue;
                }

                foreach ((array) $searchTargets as $searchTarget) {
                    if (!is_string($searchTarget) || trim($searchTarget) === '') {
                        continue;
                    }
                    $builder->orLike($searchTarget, $search);
                }
            }
            $builder->groupEnd();
        }

        $totalFiltered = $builder->countAllResults(false);

        if ($order) {
            $orderColumnName = null;
            $orderIndex = isset($order['column']) ? (int) $order['column'] : -1;
            if (isset($requestColumns[$orderIndex]['data'])) {
                $orderColumnName = $requestColumns[$orderIndex]['data'];
            }

            $orderTarget = $this->resolveOrderTarget($definitions, $orderColumnName, $orderIndex);
            if ($orderTarget) {
                $direction = strtolower((string) ($order['dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
                $builder->orderBy($orderTarget, $direction);
            }
        }

        $query = $builder->limit($length, $start)->get();
        $data  = $query->getResult();

        $result = [];
        $no = $start + 1;
        foreach ($data as $row) {
            $rowData = ['DT_RowIndex' => $no++];
            foreach ($definitions as $definition) {
                $columnName = $definition['data'];
                $rowData[$columnName] = $row->$columnName ?? null;
            }
            if (property_exists($row, 'id')) {
                $rowData['id'] = $row->id;
            }
            $result[] = $rowData;
        }

        return [
            'draw'            => intval($draw),
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data'            => $result
        ];
    }

    protected function normalizeColumns(array $columns): array
    {
        $definitions = [];

        foreach ($columns as $column) {
            if (is_string($column)) {
                $definitions[] = [
                    'data' => $column,
                    'search' => $column,
                    'order' => $column,
                ];
                continue;
            }

            if (!is_array($column) || empty($column['data']) || !is_string($column['data'])) {
                continue;
            }

            $definitions[] = [
                'data' => $column['data'],
                'search' => $column['search'] ?? $column['data'],
                'order' => $column['order'] ?? $column['data'],
            ];
        }

        return $definitions;
    }

    protected function resolveOrderTarget(array $definitions, ?string $requestDataName, int $orderIndex): ?string
    {
        if ($requestDataName !== null && $requestDataName !== '') {
            foreach ($definitions as $definition) {
                if ($definition['data'] !== $requestDataName) {
                    continue;
                }

                return is_string($definition['order']) && trim($definition['order']) !== ''
                    ? $definition['order']
                    : null;
            }
        }

        if (isset($definitions[$orderIndex])) {
            $fallbackTarget = $definitions[$orderIndex]['order'] ?? null;
            return is_string($fallbackTarget) && trim($fallbackTarget) !== ''
                ? $fallbackTarget
                : null;
        }

        return null;
    }

    // public function render($builder, array $fields)
    // {
    //     $request = $this->request;

    //     $draw   = (int) $request->getPost('draw');
    //     $start  = (int) $request->getPost('start');
    //     $length = (int) $request->getPost('length');
    //     $search = $request->getPost('search')['value'] ?? null;
    //     $order  = $request->getPost('order')[0] ?? null;

    //     /**
    //      * ==================================================
    //      * 1. TOTAL RECORD (TANPA FILTER)
    //      * ==================================================
    //      */
    //     $totalBuilder = clone $builder;
    //     $recordsTotal = $totalBuilder->countAllResults();

    //     /**
    //      * ==================================================
    //      * 2. SEARCH (HANYA KOLOM UTAMA → AMAN)
    //      * ==================================================
    //      */
    //     if (!empty($search)) {
    //         $builder->groupStart();
    //         foreach ($fields as $field) {

    //             // Abaikan kolom hasil JOIN / alias
    //             if (in_array($field, ['unit_kerja', 'unit_sk', 'jenis_jabatan', 'generasi'])) {
    //                 continue;
    //             }

    //             // Search hanya ke tabel utama (a.)
    //             $builder->orLike("a.$field", $search);
    //         }
    //         $builder->groupEnd();
    //     }

    //     /**
    //      * ==================================================
    //      * 3. TOTAL FILTERED
    //      * ==================================================
    //      */
    //     $filteredBuilder = clone $builder;
    //     $recordsFiltered = $filteredBuilder->countAllResults();

    //     /**
    //      * ==================================================
    //      * 4. ORDER BY (AMAN)
    //      * ==================================================
    //      */
    //     if ($order) {
    //         $index = (int) $order['column'];
    //         $dir   = $order['dir'] === 'desc' ? 'desc' : 'asc';

    //         // index 0 = DT_RowIndex / dtr-control
    //         if ($index > 0 && isset($fields[$index - 1])) {
    //             $field = $fields[$index - 1];

    //             // Order hanya kolom tabel utama
    //             if (!in_array($field, ['unit_kerja', 'unit_sk', 'jenis_jabatan', 'generasi'])) {
    //                 $builder->orderBy("a.$field", $dir);
    //             }
    //         }
    //     }

    //     /**
    //      * ==================================================
    //      * 5. QUERY DATA
    //      * ==================================================
    //      */
    //     $data = $builder
    //         ->limit($length, $start)
    //         ->get()
    //         ->getResultArray();

    //     /**
    //      * ==================================================
    //      * 6. FORMAT OUTPUT
    //      * ==================================================
    //      */
    //     $result = [];
    //     $no = $start + 1;

    //     foreach ($data as $row) {
    //         $rowData = [
    //             'DT_RowIndex' => $no++
    //         ];

    //         foreach ($fields as $field) {
    //             $rowData[$field] = $row[$field] ?? null;
    //         }

    //         // pastikan ID selalu tersedia (untuk tombol action)
    //         if (isset($row['id'])) {
    //             $rowData['id'] = $row['id'];
    //         }

    //         $result[] = $rowData;
    //     }

    //     /**
    //      * ==================================================
    //      * 7. RESPONSE DATATABLES
    //      * ==================================================
    //      */
    //     return [
    //         'draw'            => $draw,
    //         'recordsTotal'    => $recordsTotal,
    //         'recordsFiltered' => $recordsFiltered,
    //         'data'            => $result
    //     ];
    // }

}

