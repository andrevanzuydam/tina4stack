create table kim_version (
                                        version_id integer default 0 not null,
                                        file_name varchar (400) default '',
                                        username varchar(200) default '',
                                        date_created timestamp,
                                        content blob,
                                        release varchar(20) default 'v1.0.1',
                                        version_no integer,
                                        primary key(version_id)
                                    )