<?                                                                              
require_once("stdf.php");

$cfile = new CFile();

$sql = "

-- DROP TABLE blogs_portf;
-- DROP TABLE blogs_payed;

SELECT * from
(select 'id' as idstr, id, 'articles' as tbl, 'logo' as rw, logo as file_name, 0 as small, 'about/articles/' as dir from articles WHERE logo > '' UNION
--select 'id', id, 'blogs_msgs', 'attach' , attach, small, 'users/'||substr(login, 0, 3)||'/'||login||'/upload/' as dir from blogs_msgs INNER JOIN users ON fromuser_id=uid WHERE attach > '' UNION
select 'id', id, 'blogs_norisk', 'attach' , attach, small, 'users/'||substr(login, 0, 3)||'/'||login||'/upload/' as dir from blogs_norisk INNER JOIN users ON fromuser_id=uid WHERE attach > '' UNION
select 'id', id, 'blogs_payed', 'attach' , attach, small, 'users/'||substr(login, 0, 3)||'/'||login||'/upload/' as dir from blogs_payed INNER JOIN users ON fromuser_id=uid WHERE attach > '' UNION
select 'id', id, 'blogs_tray', 'attach' , attach, small, 'users/'||substr(login, 0, 3)||'/'||login||'/upload/' as dir from blogs_tray INNER JOIN users ON fromuser_id=uid WHERE attach > '' UNION
select 'id', blogs_msgs_attach.id, 'blogs_msgs_attach', 'name' , name, blogs_msgs_attach.small, 'users/'||substr(login, 0, 3)||'/'||login||'/upload/' as dir from blogs_msgs_attach 
	LEFT JOIN blogs_msgs ON msg_id = blogs_msgs.id INNER JOIN users ON fromuser_id=uid WHERE name > '' UNION
select 'uid', uid, 'employer', 'logo' , logo, 0, 'users/'||substr(login, 0, 3)||'/'||login||'/logo/' as dir from employer WHERE logo > '' UNION
select 'uid', uid, 'freelancer', 'resume_file' , resume_file, 0, 'users/'||substr(login, 0, 3)||'/'||login||'/resume/' as dir from freelancer WHERE logo > '' UNION
select 'id', holidays_messages.id, 'holidays_messages', 'image' , image, 0, 'users/'||substr(login, 0, 3)||'/'||login||'/upload/' as dir from holidays_messages INNER JOIN users ON fromuser=uid WHERE image > '' UNION
select 'id', commune.id, 'commune', 'image' , image, small::integer, 'users/'||substr(login, 0, 3)||'/'||login||'/upload/' as dir from commune INNER JOIN users ON author_id=uid WHERE image > '' UNION
select 'id', commune_messages.id, 'commune_messages', 'attach' , attach, small::integer, 'users/'||substr(login, 0, 3)||'/'||login||'/upload/' as dir from commune_messages INNER JOIN users ON user_id=uid WHERE attach > '' UNION
select 'id', norisk_attach.id, 'norisk_attach', 'name' , name, 0, 'users/'||substr(login, 0, 3)||'/'||login||'/upload/' as dir from norisk_attach 
	LEFT JOIN norisk ON norisk_id = norisk.id INNER JOIN users ON emp_id=uid WHERE name > '' UNION
select 'id', messages.id, 'messages', 'attach' , attach, 0, 'users/'||substr(login, 0, 3)||'/'||login||'/upload/' as dir from messages INNER JOIN users ON from_id=uid WHERE attach > '' UNION
select 'id', ban_banners.id, 'ban_banners', 'filename' , filename, 0, 'banners/' from ban_banners WHERE filename > '' UNION
select 'id', team_people.id, 'team_people', 'userpic' , userpic, 0, 'team/' from team_people WHERE userpic > '' UNION
select 'id', interview.id, 'interview', 'file1' , file1, pt1, 'users/'||substr(login, 0, 3)||'/'||login||'/upload/' as dir from interview INNER JOIN users ON from_id=uid WHERE file1 > '' UNION
select 'id', interview.id, 'interview', 'file2' , file2, pt2, 'users/'||substr(login, 0, 3)||'/'||login||'/upload/' as dir from interview INNER JOIN users ON from_id=uid WHERE file2 > '' UNION
select 'id', interview.id, 'interview', 'file3' , file3, pt3, 'users/'||substr(login, 0, 3)||'/'||login||'/upload/' as dir from interview INNER JOIN users ON from_id=uid WHERE file3 > '' UNION
select 'id', interview.id, 'interview', 'file4' , file4, pt4, 'users/'||substr(login, 0, 3)||'/'||login||'/upload/' as dir from interview INNER JOIN users ON from_id=uid WHERE file4 > '' UNION
select 'id', interview.id, 'interview', 'file5' , file5, pt5, 'users/'||substr(login, 0, 3)||'/'||login||'/upload/' as dir from interview INNER JOIN users ON from_id=uid WHERE file5 > '' UNION
select 'id', interview.id, 'interview', 'file6' , file6, pt6, 'users/'||substr(login, 0, 3)||'/'||login||'/upload/' as dir from interview INNER JOIN users ON from_id=uid WHERE file6 > '' UNION
select 'id', interview.id, 'interview', 'file7' , file7, pt7, 'users/'||substr(login, 0, 3)||'/'||login||'/upload/' as dir from interview INNER JOIN users ON from_id=uid WHERE file7 > '' UNION
select 'id', portfolio.id, 'portfolio', 'prev_pict' , prev_pict, 0, 'users/'||substr(login, 0, 3)||'/'||login||'/upload/' as dir from portfolio INNER JOIN users ON user_id=uid WHERE prev_pict > '' UNION
select 'id', portfolio.id, 'portfolio', 'pict' , pict, 0, 'users/'||substr(login, 0, 3)||'/'||login||'/upload/' as dir from portfolio INNER JOIN users ON user_id=uid WHERE pict > '' UNION
select 'id', press.id, 'press', 'logo' , logo, 0, 'about/press/' as dir from press WHERE logo > '' UNION
select 'id', project_attach.id, 'project_attach', 'name' , project_attach.name, 0, 'users/'||substr(login, 0, 3)||'/'||login||'/upload/' as dir from project_attach 
	LEFT JOIN projects ON project_id = projects.id INNER JOIN users ON user_id=uid WHERE project_attach.name > '' UNION
select 'id', projects.id, 'projects', 'filename' , filename, 0, 'users/'||substr(login, 0, 3)||'/'||login||'/upload/' as dir from projects INNER JOIN users ON user_id=uid WHERE filename > '' UNION
select 'id', shop.id, 'shop', 'attach' , attach, small, 'users/'||substr(login, 0, 3)||'/'||login||'/upload/' as dir from shop INNER JOIN users ON from_user=uid WHERE attach > '' UNION
select 'id', sopinions.id, 'sopinions', 'logo' , logo as file_name, 0, 'about/opinions/' as dir from sopinions  WHERE logo > '' UNION
select 'id', projects_offers_attach.id, 'projects_offers_attach', 'prev_pict' , prev_pict, 0, 'users/'||substr(login, 0, 3)||'/'||login||'/upload/' as dir from projects_offers_attach 
	LEFT JOIN projects_offers ON offer_id = projects_offers.id INNER JOIN users ON user_id=uid WHERE prev_pict > '' UNION
select 'id', projects_offers_attach.id, 'projects_offers_attach', 'pict' , pict, 0, 'users/'||substr(login, 0, 3)||'/'||login||'/upload/' as dir from projects_offers_attach 
	LEFT JOIN projects_offers ON offer_id = projects_offers.id INNER JOIN users ON user_id=uid WHERE pict > '' UNION
select 'uid', uid, 'users', 'photo' , photo, 0, 'users/'||substr(login, 0, 3)||'/'||login||'/foto/' as dir from users WHERE photo > ''
) as d

WHERE (file_name, dir) NOT IN (SELECT fname, path FROM file)
";

$res = pg_query(DBConnect(), $sql);
$all = pg_num_rows($res);
print "Всего строк = ".$all."\n";
$i = 0;
if ($all) while ($row = pg_fetch_assoc($res)){
    $i++;
    if ($row['file_name'] && !$cfile->DBImport($row['dir'].$row['file_name'])){ //файл не существует, но в базе есть ссылка
        $sql = "UPDATE ".$row['tbl']." SET ".$row['rw']." = NULL WHERE ".$row['idstr']."=".$row['id'];
        pg_query(DBConnect(), $sql);
    }
if ($i % 1000 == 0) print "Строка = ". $i."\n";        
}