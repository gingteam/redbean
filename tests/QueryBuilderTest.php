<?php

use GingTeam\RedBean\Facade as R;

beforeAll(function() {
    file_put_contents(__DIR__.'/test.db', '');
    R::setup('sqlite:'.__DIR__.'/test.db');
});

test('test 1', function () {
    $qb = R::createQueryBuilder();
    [$sql, $bindings] = $qb->start()
        ->select('*')
        ->from('book')
        ->where('id > ?')
            ->put(1)
        ->dump();

    $this->assertSame('SELECT * FROM book WHERE id > ?', $sql);
    $this->assertCount(1, $bindings);
});

test('test 2', function () {
    $author = R::dispense('author');
    $author->name = 'Vũ Trọng Phụng';
    R::store($author);

    $book = R::dispense('book');
    $book->name = 'Làm Đĩ';
    $book->author = $author;
    R::store($book);

    $qb = R::createQueryBuilder();

    $result = $qb->start()
        ->select('*')
        ->from('book')
        ->get();

    $this->assertCount(1, $result);
    $this->assertSame('Vũ Trọng Phụng', $book->author->name);
});
