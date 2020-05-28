<?php
namespace App\Contracts;

interface ISearchAccessType
{
    public function search($filterParams, $sortByParams);
}
