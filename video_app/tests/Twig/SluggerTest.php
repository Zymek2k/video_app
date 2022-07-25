<?php

namespace App\Tests\Twig;

use PHPUnit\Framework\TestCase;
use App\Twig\SlugifyFilterExtension;

class SluggerTest extends TestCase
{
    /**
     * @dataProvider getSlugs
     */
    public function testSlugify(string $tested, string $slug): void
    {
        $slugger = new SlugifyFilterExtension;
        $this->assertSame($slug, $slugger->slugify($tested));
    }

    public function getSlugs()
    {
        yield  ['Lorem Ipsum', 'lorem-ipsum'];
        yield [' Lorem Ipsum', 'lorem-ipsum'];
        yield [' lOrEm  iPsUm  ', 'lorem-ipsum'];
        yield ['!Lorem Ipsum!', 'lorem-ipsum'];
        yield ['lorem-ipsum', 'lorem-ipsum'];
        yield ['Children\'s books', 'childrens-books'];
        yield ['Five star movies', 'five-star-movies'];
        yield ['Adults 60+', 'adults-60'];
    }
}
