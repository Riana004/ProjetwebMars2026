create table pages (
    id serial primary key,
    slug varchar(255) not null unique,
    title varchar(255) not null,
    content text not null,
    meta_desc text,
    alt_images varchar(255)
);