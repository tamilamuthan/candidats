function language()
{
    this.lang={};
    this.lang["customer"]="lead";
    this.lang["customers"]="leads";
    this.lang["Customer"]="Lead";
    this.lang["Customers"]="Leads";
    this.getLang=function(langname)
    {
        return this.lang[langname]?this.lang[langname]:langname;
    };
}