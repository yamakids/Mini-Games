create database game_php;
grant all on game_php.* to  ユーザー  @localhost identified by  パスワード;
use game_php;

create table users (
    id int not null auto_increment,
    name varchar(255),
    email varchar(255) unique,
    password varchar(255),
    created datetime,
    modified datetime,
    score_p int,
    time int,
    score_m int,
    primary key(id)
);