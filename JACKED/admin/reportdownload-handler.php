<?php

    header('Content-Type: application/csv');
    header('Content-Disposition: attachment; filename=Purveyor Sales Report.csv');
    header('Pragma: no-cache');
    
    $JACKED = new JACKED(array('admin', 'MySQL', 'DatasBeard'));

    if(!$JACKED->admin->checkLogin()){
        header('HTTP/1.1 401 Unauthorized');
        exit();
    }

    $output = fopen('php://output', 'w');

    $dateParts = array_map('trim', explode('-', $_GET['dateRange']));
    $startDate = strtotime($dateParts[0] . ' midnight');
    $endDate = strtotime($dateParts[1] . ' 11:59:59 PM');

    $timeCriteria = ' Sale.timestamp >= ' . $startDate . ' AND Sale.timestamp <= ' . $endDate;

    $reportMap = $JACKED->DatasBeard->getRows($_GET['reportId']);

    $query = 'SELECT ';
    $fields = array();
    $querymap = array(
        'FROM' => null,
        'WHERE' => null,
        'ORDERBY' => null
    );
    foreach($reportMap as $map){
        if(substr($map['key'], 0, 1) !== '_'){
            $fields[] = $map['value'] . ' AS ' . $map['key'];
        }else{
            if(substr($map['key'], 0, 9) !== '_QUERYMAP_'){
                $querymap[substr($map['key'], 10)] = $map['value'];
            }else{
                // wtf even is this
            }
        }
    }
    $query .= implode(', ', $fields);

    if($querymap['FROM']){
        $query .= ' FROM ' . $querymap['FROM'];
    }

    if($querymap['WHERE']){
        $query .= ' WHERE ' . $querymap['WHERE'] . ' AND ' . $timeCriteria;
    }else{
        $query .= ' WHERE ' . $timeCriteria;
    }

    if($querymap['ORDERBY']){
        $query .= ' ORDER BY ' . $querymap['ORDERBY'];
    }

    $result = $JACKED->MySQL->query($query);
    if($result){
        $keys = array();
        foreach($result[0] as $key => $row){
            $keys[] = $key;
        }
        fputcsv($output, $keys);
        foreach($result as $row){
            fputcsv($output, $row);
        }
    }else{
        header('HTTP/1.1 500 Internal Server Error');
        echo $JACKED->MySQL->getError();
    }

    exit();

?>