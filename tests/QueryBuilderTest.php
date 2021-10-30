<?php

use GingTeam\RedBean\Facade as R;

beforeAll(function() {
    file_put_contents(__DIR__.'/test.db', '');
    R::setup('sqlite:'.__DIR__.'/test.db');
});

test('test 1', function () {
    $qb = R::createQueryBuilder();
    [$sql, $bindings] = $qb
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

    // insert
    $qb->insert()
        ->into('book (name, author_id)')
        ->values('(?, ?)')
            ->put('Book #2')
            ->put(1)
        ->execute();
    $lastId = $qb->getLastInsertId();

    // update
    $qb->update('book')
        ->set('name = ?')
            ->put('Book #1')
        ->where('id = ?')
            ->put(1)
        ->execute();

    $result = $qb
        ->select('*')
        ->from('book')
        ->get();

    $book1 = $qb
        ->select('name')
        ->from('book')
        ->where('id = ?')
            ->put(1)
        ->getOne();

    $this->assertCount(2, $result);
    $this->assertSame('Vũ Trọng Phụng', $book->author->name);
    $this->assertSame('Book #1', $book1['name']);
    $this->assertEquals(2, $lastId);
});

test('test 3', function () {
    $qb = R::createQueryBuilder();

    $sql = $qb
        ->select('*')
        ->from('book')
        ->where('id = ?')
            ->put(1);

    $this->assertSame('SELECT * FROM book WHERE id = ?', $sql->toSql());
    $qb->reset();
    $this->assertEmpty($sql->toSql());
});
