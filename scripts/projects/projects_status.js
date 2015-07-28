function ProjectsStatus()
{
    ProjectsStatus=this; // ie ругался без этого, пока не понял.
    
    
    //--------------------------------------------------------------------------
    
    //Начальная инициализация
    this.init = function() 
    {
    };
    
    //--------------------------------------------------------------------------
    
    
    this.changeStatus = function(project_id, status, hash)
    {      
        //@todo: Почему-то xajax переводит в double?
        var param = {
            project_id:project_id.toString(),  
            status:status,
            hash:hash
        };

        xajax_changeProjectStatus(param);
    };
    
   
    //--------------------------------------------------------------------------
    
    
    //Запуск инициализации
    this.init();    
}

window.addEvent('domready', function() {
    new ProjectsStatus();
});