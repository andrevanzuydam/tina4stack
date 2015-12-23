/**
 * Created by s946115 on 2015/12/23.
 */
function tina4_snippets() {
    return [{
        name: "lookup",
    content: "lookup=${1:ns_field}:lookup_table=${2:oa_table}:lookup_by=${3:oa_field}:lookup_return=${4:oa_field}",
    tabTrigger: "lookup"
    },
    {
        name: "dropdown",
        content: "<${1:oa_field} ${2:ns_field}>\n    ${3:ns_value} ${4:oa_value}\n</${1}>\n",
        tabTrigger: "dropdown"
    },
    {
        name: "Ruth::addRoute",
        content : "Ruth::addRoute(${1:RUTH_GET}, \"${2:path}\", function() {\n ${3:code}\n});\n\n",
        tabTrigger : "ruthadd"
    }
    ];
}