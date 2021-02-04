<?php namespace App\Controllers;

use \PhpOffice\PhpSpreadsheet\IOFactory;

class Api extends BaseController
{
    protected $request;
    protected $tableName = 'my_table_name';

    /**
     * 미리보기 데이터를 생성합니다.
     * 
     * @param   void
     * @return  json $data
     */
    public function preview()
    {
        $file = $this->request->getFile('my-file');
        try {

            // 파일이 없을 때
            if (! $file->isValid()) {
                // throw new \RuntimeException($file->getErrorString().'('.$file->getError().')');
                throw new \RuntimeException('파일이 없습니다.');
            }

            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file);

            // 엑셀 데이터를 배열에 담는다.
            $sheetData = $spreadsheet->getActiveSheet()->toArray();
            array_shift($sheetData);

            // 대성쿠폰 테이블의 max id를 읽어온다.
            $db = \Config\Database::connect();
            $builder = $db->table($this->tableName);
            $builder->selectMax('id');
            $query = $builder->get();
            $maxId = $query->getRow()->id;

            $response = [
                'code'      => 200,
                'data'      => $sheetData,
                'render'    => $this->twig->render('pages/preview.html', ['sheetData' => $sheetData, 'maxId' => $maxId]),
                'maxId'     => $maxId
            ];

        } catch (\RuntimeException $e) {
            $response = [
                'code' => 401,
                'message' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            $response = [
                'code' => 409,
                'message' => $e->getMessage()
            ];
        }

        return $this->response->setJSON($response);
    }

    /**
     * 쿠폰을 등록합니다.
     * 
     * @param   void
     * @return  json $response
     */
    public function coupon()
    {
        $formData = $this->request->getPost();

        $maxId = $formData['maxId'];
        $rows = json_decode($formData['data']);

        // 테이블에 등록할 데이터를 만든다.
        $batchData = [];
        $i = 1;
        foreach($rows as $row) {
            // $row[0] => 쿠폰번호
            // $row[1] => 쿠폰상태 ('활성')
            array_push($batchData, [
                'num'           => (int)$maxId + $i,
                'coupon_number' => $row[0],
                'wr_id'         => 1,
                'branch_name'   => ''
            ]);
            $i++;
        }

        try {
            // 등록 시도
            $db = \Config\Database::connect();
            $builder = $db->table($this->tableName);
            $result = $builder->insertBatch($batchData);
            $response = [
                'code'      => 200,
                'message'   => '등록되었습니다.'
            ];
        } catch (\Exception $e) {
            $response = [
                'code'      => 400,
                'message'   => $e->getMessage()
            ];
        }

        return $this->response->setJSON($response);
    }
}
