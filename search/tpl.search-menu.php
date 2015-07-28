<ul class="search-tabs" data-ga_role="<?=(is_emp() ? 'Employer' : (get_uid(false) ? 'Freelance' : 'Unauthorized'))?>">
    <li <?= $type=='users'?'class="active"':''?>>
        <a href="/search/?type=users" data-ga_type="performer">
            Поиск <span class="b-page__desktop b-page__ipad">исполнителя</span><div class="b-page__iphone">исполнителя</div>
        </a>
    </li>
    <li <?= $type=='projects'?'class="active"':''?>>
        <a href="/search/?type=projects" data-ga_type="project">
            Поиск <span class="b-page__desktop b-page__ipad">проекта</span><div class="b-page__iphone">проекта</div>
        </a>
    </li>
    <li <?= $sections?'class="active"':''?>>
        <a href="/search/?type=works" data-ga_type="section">
            Поиск по <span class="b-page__desktop b-page__ipad">разделам сайта</span><div class="b-page__iphone">разделам сайта</div>
        </a>
    </li>
</ul>