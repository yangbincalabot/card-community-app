<?php

namespace App\Imports;

use App\Models\Carte;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class CarteImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        $carte = new Carte();
        $userModel = new User();
        foreach ($rows as $key => $row)
        {
            if ($key == 0) {
                continue;
            }
            $name = $row[1] ?? '';
            $phone = $row[4] ?? '';
            if (empty($row) || empty($name) || empty($phone)) {
                continue;
            }
            $carteInfo = Carte::query()->where(['name' => $name, 'phone' => $phone])->first();
            if (!empty($carteInfo)) {
                continue;
            }
            $uid = 0;
            $avatar = getletterAvatar($name);
            $user = $userModel::query()->where('phone', $phone)->first();
            if (!empty($user)) {
                $oldInfo = $carte::query()->where('uid', $user['id'])->first();
                if (empty($oldInfo)) {
                    $uid = $user['id'];
                    $avatar = $user['avatar'];
                }
            }
            $createData = [
                'uid' => $uid,
                'company_name'     => $row[0],
                'name'    => $name,
                'avatar'    => $avatar,
                'position' => $row[3],
                'phone' => $phone,
                'email' => $row[5],
                'address_title' => $row[6],
                'introduction' => $row[8],
            ];
            $carte::query()->create($createData);
        }
    }
}

