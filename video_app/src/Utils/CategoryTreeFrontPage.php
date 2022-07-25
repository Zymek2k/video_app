<?php
namespace App\Utils;

use App\Twig\SlugifyFilterExtension;
use App\Utils\AbstractClasses\CategoryTreeAbstract;

class CategoryTreeFrontPage extends CategoryTreeAbstract {

    public function getCategoryListAndParent(int $id): string
    {
        $this->slugger = new SlugifyFilterExtension;
        $parentData = $this->getMainParent($id);
        $this->mainParentName = $parentData['name'];
        $this->mainParentId = $parentData['id'];
        $key = array_search($id, array_column($this->categoriesArrayFromDb, 'id'));
        $this->currentCategoryName = $this->categoriesArrayFromDb[$key]['name'];
        $categories_array = $this->buildTree($parentData['id']);
        return $this->getCategoryList($categories_array);
    }

    public function getCategoryList(array $categories_array)
    {
        $this->categoryList .= '<ul>';
        foreach ($categories_array as $value)
        {
            $catName = $this->slugger->slugify($value['name']);
            $url = $this->urlgenerator->generate('video_list', ['categoryname' => $catName, 'id' => $value['id']]);
            $this->categoryList .= '<li>' . '<a href="' . $url . '">' . $value['name'] . '</a>';
            if (!empty($value['children']))
            {
                $this->getCategoryList($value['children']);
            }
            $this->categoryList .= '</li>';
        }
        $this->categoryList .= '</ul>';
        return $this->categoryList;
    }

    public function getMainParent(int $id): array
    {
        $key = array_search($id, array_column($this->categoriesArrayFromDb, 'id'));
        if($this->categoriesArrayFromDb[$key]['parent_id'] != null)
        {
            return $this->getMainParent($this->categoriesArrayFromDb[$key]['parent_id']);
        }
        else
        {
            return [
                'id'=>$this->categoriesArrayFromDb[$key]['id'],
                'name'=>$this->categoriesArrayFromDb[$key]['name'],
                ];
        }
    }

    public function getChildIds(int $parent): array
    {
        static $ids = [];
        foreach($this->categoriesArrayFromDb as $val)
        {
            if($val['parent_id'] == $parent)
            {
                $ids[] = $val['id'].',';
                $this->getChildIds($val['id']);
            }
        }

        return $ids;
    }
}