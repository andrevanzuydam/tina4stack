Unreleased
----------

```
```

2014-10-29: Version 2.8.0
-------------------------

```
ADDED:     Argument "--cache-folder" to indicate where the cache is stored
ADDED:     `phpdoc self-update` command to the PHAR archive to update phpDocumentor with a single command
FIXED:     #423: Error report in responsive and responsive-twig template should hide empty results and update error
           counter next to the filename.
FIXED:     #573: Visibility filters in 'responsive' and 'responsive-twig' do not function properly
FIXED:     Several scrutinizer reported clean ups
FIXED:     If tmp was somewhere in path it would be incorrectly replaced
FIXED:     Several tests were added
FIXED:     It is now possible to install phpDocumentor using composer in a project using ZF 2.3 or higher
FIXED:     Fatal error: Call to a member function getParent() on a non-object in ConstantDescriptor.php
FIXED:     Fatal error: Call to a member function getParent() on a non-object in PropertyDescriptor.php
REMOVED:   Knp menu from composer.json because it was not used
```

2014/08/18: Version 2.7.0
-------------------------

```
ADDED:     A new writer that outputs a statistical extract from the collected data
ADDED:     Windows support for Ansible playbooks
ADDED:     8% to 10% Code Coverage thanks to #testfest 2014
ADDED:     #1347: Support for custom Vendor folders
ADDED:     Plugins can now be configured using parameters.
ADDED:     Complete support for @example
ADDED:     NamespacePrefix to LegacyNamespaceConverter plugin
FIXED:     Fatal error in MethodDescriptor when a parent was incorrectly called
FIXED:     Notice in '@see' handling
FIXED:     #1349: Configuration file was not found in working directory
FIXED:     Phing integration by re-instating the bootstrap class
FIXED:     Fixing type inference and variable length issues
FIXED:     Whether a method is inherited is shown again in XSL-based templates
FIXED:     Various minor bugs that became visible during the writing of tests
FIXED:     #1390: Source code paths were incorrect if the source was in folder
FIXED:     #1341: Icons in clean template were shown incorrectly
FIXED:     #1331: Not all validations were properly shown
FIXED:     #1077: Spaces in a path won't trip up libxml anymore
CHANGED:   Changed reference to deprecated Parsedown method parse() to text()
CHANGED:   Completely replaced Puppet with Ansible to provision contributor VMs
CHANGED:   Simpler provisioning for generic contributors; to do profiling
           another task is now needed
CHANGED:   Docs no longer refer to Short Description or Long Description but to
           Summary and Description per PSR-5
```

2014/07/09: Version 2.6.1
-------------------------

```
FIXED:     #1330: Fix crash when assembling package tags
FIXED:     #1326: Fix crash while generating routes in Twig templates
```

2014/06/27: Version 2.6.0
-------------------------

```
ADDED:     #1087: Fully support `@see`
ADDED:     #1213: Resolve inline @see and @link to Markdown link
CHANGED:   #1186: Move checking of writer requirements
CHANGED:   #1267: XSL should use Router
CHANGED:   Update contribution guide to match new XHGUI
CHANGED:   #1248: Replace ZendConfig with Serializer-based config
CHANGED:   #1017: Updated help output for --hidden to be more descriptive
CHANGED:   #1264: Clean template no longer requires an internet connection
CHANGED:   #1212: Plugin configuration can now be loaded from a config file
CHANGED:   #1194: Add support for default parameter values in @method
FIXED:     #1313: Namespace Aliases are not stored on FileDescriptor
FIXED:     #1308: Multiple templates in configuration do not work
FIXED:     #713: XSL Templates are not found in Windows
FIXED:     #1253: Transformation crashes on typehint
FIXED:     #1268: Restore handling of visibility
FIXED:     #1130: Arrays not resolved in documentation
FIXED:     #1278: Template is not read from configuration
FIXED:     #1239: Parseprivate always triggers a complete parsing run
FIXED:     Parsing crashes on visibility as string
FIXED:     Routing crashes in Twig
FIXED:     #1307: Parsing crashes on unknown trait
FIXED:     #1114: Debug mode for Twig was not enabled
FIXED:     Errors were not shown in XSL templates
FIXED:     Remove duplicate namespace separator in constants defined by DEFINE
FIXED:     #930: @see and @link did not resolve self
FIXED:     #993: UTF-8 characters in filenames do not work in windows
FIXED:     #790: Inheritance in XSL was incorrectly registered
FIXED:     #713: Phar was sometimes not working on windows due to paths
FIXED:     Restore Behat tests
FIXED:     #1252: Namespaces are not shown on responsive template
```

2014/05/17: Version 2.5.0
-------------------------

```
FIXED:     #1211: Loading a single plugin is not possible
FIXED:     #1232: Routing crashes on magic property in trait
FIXED:     #157: Classes do not inherit trait methods and props
FIXED:     #1193: Package tags don't inherit to classes
FIXED:     #1229: @method tag in a trait causes Exception
FIXED:     #1196: Some files cannot be copied with FileIO
CHANGED:   Better OPcache handling, annotations are not stripped anymore in PHP 5.5+
CHANGED:   phpdoc.php was renamed to phpdoc; phpdoc.php is kept for backwards compatibility
```

2014/04/01: Version 2.4.0
-------------------------

```
FIXED:     #1141: Deprecated report was missing in Clean template.
FIXED:     #1191: Opcache comments were not disabled due to incorrect extension name
FIXED:     #1184, #1181: @package tag on File was not recognized and not inherited on children
FIXED:     #1180: phpDocumentor crashes if an `@subpackage` has no name set
FIXED:     #1178: Generating a marker listing crashes sometimes
FIXED:     #1176: DocBlocks were not overridden on subclasses
FIXED:     #1163: Responsive and Responsive-twig templates do not work on HTTPS
FIXED:     #1158: Clean and Responsive template shows empty namespace menu when there are no namespaces
FIXED:     #1134, #1132: GraphViz reports can crash due to empty labels
FIXED:     #1098: Re-added missing Javascript file
FIXED:     #1037: IE did not like empty anchors
FIXED:     #152:  @internal and @ignore did not behave as expected
FIXED:     Javascript notices in Clean by upgrading jQuery
ADDED:     #1141: Deprecated report to the Clean template
ADDED:     #629:  Support for Variadics
CHANGED:   #1099, #1101: Replaced custom PHAR compiling with box-project.org and fixing issues in the mean time
```

2014/03/07: Version 2.3.2
-------------------------

```
FIXED:     #1133: Fixed subpackage without package validation
FIXED:     #1120: Set stable versions of DOMPDF and ezcDocument in Composer.json
```

2014/02/26: Version 2.3.1
-------------------------

```
CHANGED:   #1128: Minimum Symfony/Console version was set at 2.3 to support LTS version.
FIXED:     #1090: There were still GraphViz issues, these have now been fixed and confirmed.
FIXED:     #1131: Fatal Error on ConstantConverter when using an XML-based template.
```

2014/02/16: Version 2.3
-----------------------

```
ADDED:     Support for writing PDF files using a Twig template by adding the PDF writer.
ADDED:     More documentation on types, running phpDocumentor, the @var tag.
ADDED:     Support for sorting lists with Twig and XSLT.
ADDED:     Basic support for generating reference documentation with ReST documents.
CHANGED:   Replaced custom phar building with the library from http://box-project.org.
FIXED:     #1090: GraphViz issues where the global namespace caused GraphViz not to generate.
FIXED:     #1037 by @siad007: if opcode cache is enabled for CLI then annotations are dropped.
FIXED:     #1031: Fixed inheritance for the summary, description, tags and the way @inheritdoc works.
FIXED:     Adding several unit tests.
FIXED:     Cleaned up code to remove warnings and errors.
FIXED:     #1111: An infinite loop occurred during processing of global constants.
FIXED:     Crash that occurred when building a tree of packages.
FIXED:     Crash that occurred when an interface was not recognized.
FIXED:     XSLTCache extension could not be used instead of XSLTProcessor due to a stray typehint.
FIXED:     #1110: Crash on incomplete `define` definition.
FIXED:     #949: `@inheritdoc` is not working with interfaces.
```

2013/11/23: Version 2.2
-----------------------

```
ADDED:     Information how to donate to phpDocumentor using gittip
ADDED:     Profiling information and process
CHANGED:   Moved the external class documentation code to their own router
CHANGED:   #1080: Replaced MarkDownExtra dependency with Parsedown
CHANGED:   Rewrote a lot of the documentation; still a work in progress
FIXED:     #720: External class documentation had ../files prepended
FIXED:     #932: Todo tags did not show up in Twig templates
FIXED:     #1049: Arguments passed by reference did not show up as being by reference
FIXED:     #1075: Prevent output from wrapping in Windows console
FIXED:     Cleaned up code based on Continuous Integration messages
FIXED:     Timezone issues on some machines
FIXED:     Various issues in several templates
```

2013/09/28: Version 2.1
-----------------------

```
ADDED:     New cli option 'log' to tell phpDocumentor the path where to log to
ADDED:     Verbosity can now be provided in three level, each indicating how much is written in the log
ADDED:     Support for the XSLCache drop in replacement of ext/xsl
ADDED:     Locale can now be supplied in the configuration
ADDED:     German translation for error messages
ADDED:     Support to insert Markdown files into specific points of the outputted documentation (partials)
ADDED:     Deprecated tag now registers the version number since when the associated element was deprecated
ADDED:     Show which traits are consumed by a class
ADDED:     Link to the traits that are consumed by a class
ADDED:     Show traits with their methods and properties
CHANGED:   phpDocumentor now checks for transformation requirements at the start of the application (#148)
FIXED:     Fatal error with some of the old-style validations
FIXED:     Template:list command errors due to an incorrect path
FIXED:     Checkstyle error report only showed errors of the file itself and not subelements (#1046)
FIXED:     Validation to check for validity of return types
FIXED:     Validation to check for validity of parameters
FIXED:     Fatal error when a parent interface is not in the project
FIXED:     Template:list does not throw an error
FIXED:     XML output correctly exposes @see, @link, @version
FIXED:     XML output now does not choke on special characters
FIXED:     Error code for return type was incorrect
FIXED:     Functions in the responsive twig now unfold to show complete contents
FIXED:     Constants in the responsive twig now unfold to show complete contents
FIXED:     Using an ampersand in the type of an @param no longer causes a fatal (not a recommended practice!)
REMOVED:   #814; removed ParserAbstract base class for Parser
REMOVED:   PSR Draft is now moved to its own repo: https://github.com/phpDocumentor/fig-standards/blob/master/proposed/phpdoc.md
REMOVED:   Automatic generation of log files, the new log option can be used or a configuration option
```

2013/08/08: Version 2.0.1
-------------------------

```
FIXED:      Generated phar files could not be set to executable and ran.
FIXED:      Missing File-level DocBlocks were not detected.
FIXED:      Classes and namespace were not generated in Windows.
FIXED:      Notices were thrown with the Clean template.
FIXED:      In windows were path calculated incorrectly.
```

2013/08/03: Version 2.0.0
-------------------------

```
ADDED:      Presentation mode to router Twig filter, allows for different representations
ADDED:      StandardRouter now also supports generating paths for file documentation pages.
ADDED:      Version tag is now processed in the descriptors
FIXED:      Generated paths in the Twig writer were not windows safe
FIXED:      Minor Descriptor tweaks
FIXED:      Inheritance was broken due to an erroneous merge
FIXED:      Ampersands in tag descriptions caused XSL based templates to fatal
FIXED:      Inheritance of methods in interfaces was broken
FIXED:      All elements had an internal and ignore tag added due to an error in filtering
FIXED:      @internal inline tag handling did not function
FIXED:      Fatal error when an argument in an @method tag does not have a type
FIXED:      The logging directives in the configuration file were not followed.
CHANGED:    When installing composer the template folder will be vendor/phpdocumentor/templates and not data/templates
CHANGED:    The included ProgressHelper was replaced with the new ProgressHelper of Symfony Console
            Components (https://github.com/symfony/symfony/pull/3501).
REMOVED:    Installer is removed; proved too unreliable
DEPRECATED: Previously shared assets could be in the /data folder; this unnecessarily complicated template handling
            and composer integration. Shared assets have now been moved inside the templates and when a template
            requests shared assets it is in fact requesting files from the abstract template.
            Using a source attribute with a transformation that has no direct reference to a template will be removed
            in version 3.0; until that point the code will trigger E_USER_DEPRECATED warnings.
```

2013/07/12: Version 2.0.0b7
---------------------------

```
FIXED:     Warning in browser console 'Viewport argument value "device-width;" for key "width" is invalid, and has
           been ignored. Note that ';' is not a separator in viewport values. The list should be comma-separated.'
FIXED:     VERSION file was missing from phar archive, causing it to fail
FIXED:     Elements with an @ignore tag were not filtered
FIXED:     Deprecated elements are now striken in the class overview
FIXED:     The @see reference was not shown and interpreted
FIXED:     The @uses reference was not shown and interpreted
FIXED:     Response type was not shown with magic methods
FIXED:     Arguments were not shown with magic methods
FIXED:     Type is not shown with magic properties
FIXED:     Magic methods were missing from sidebar
FIXED:     Coding standards issues
FIXED:     Several documentation issues
FIXED:     Windows error where the directory slashes were incorrectly presented.
FIXED:     When a file contains multiple errors, only the first is shown.
FIXED:     Generating a new template gave a fatal error
FIXED:     Generated templates were missing the transformation line for their structure.xml
FIXED:     Linking to functions
FIXED:     Linking to constants
FIXED:     Linking to properties
FIXED:     Linking to methods
FIXED:     Root elements with the same shortname and file but in a different namespace would overwrite the other.
ADDED:     New template 'xml' for outputting a pure XML representation of the project's structure
ADDED:     Update instructions to issue a PR against homebrew if the phar file updates:
           https://github.com/josegonzalez/homebrew-php/blob/master/Formula/phpdocumentor.rb
CHANGED:   Assembling of Tags to Descriptors was refactored to work the same way as other Descriptors.
CHANGED:   Properties won't emit an error regarding a missing summary if it has an @var tag with a description.
```

2013/06/23: Version 2.0.0b6
---------------------------

```
ADDED:     Travis configuration was changed to test against 5.5
FIXED:     Packages were not indexed and shown correctly.
FIXED:     @var descriptions were not shown as summary if the summary was absent for a property.
FIXED:     Added static label on a property in the responsive template.
FIXED:     Alignment of tags in table display.
FIXED:     Response information was missing from method description.
FIXED:     Sourcecode viewer in new-black template.
FIXED:     Magic methods are now shown and inherited in the class view for the responsive-twig template.
FIXED:     Magic properties are now shown and inherited in the class view for the responsive-twig template.
FIXED:     Markdown fencing in responsive and responsive-twig template now correctly indents code examples.
CHANGED:   Deep link should not be shown for members without location.
CHANGED:   phpDocumentor now sets the memory limit to -1 to prevent issues due to a limited memory usage.
CHANGED:   Bumped maximum recursion setting from 5.000 to 10.000 since errors were still reported.
REMOVED:   `/bin/parse.php` binary; its function is carried out by `phpdoc project:parse`.
REMOVED:   `/bin/transform.php` binary; its function is carried out by `phpdoc project:transform`.
```

2013/06/21: Version 2.0.0b5
---------------------------

```
FIXED:     Root namespace was named `global` in overviews
FIXED:     An empty `Global ('\')` entry pointed to a non-existing default.html in the index
FIXED:     Since tag now shows the version number
FIXED:     Fatal error when an interface's parent could not be resolved in this project
FIXED:     API Documentation menu remains empty
FIXED:     Interface parents now link to the rest of the documentation
FIXED:     Inheritance of methods, constants and properties was not correctly recognized; this is now fixed
FIXED:     When a method argument has a typehint but no @param tag then the typehint will be shown
FIXED      Fatal error in XSL based templates when an interface extends another
```

2013/06/16: Version 2.0.0b4
---------------------------

```
FIXED      Fatal error that occurs when a constant has an error
FIXED      Fatal error that occurs in certain cases with the getError() method
FIXED      Refactored Builder into Assemblers to reduce technical debt
CHANGED    Refactored ProjectDescriptor Builder to separate assembling from filtering and validating
CHANGED    Introduced Symfony Validator component for element validation
CHANGED    Introduced ZF2 Filter component to filter elements (for example with @internal)
```

2013/06/14: Version 2.0.0b3
---------------------------

```
FIXED      Using an @uses tag results in a fatal error during execution
FIXED      Errors are now shown on the errors report of the responsive-twig template
FIXED      The error count on the index page of the responsive-twig template is restored
FIXED      Checkstyle output now functions as expected
FIXED      new-black template failed due to a capitalization issue with the Sourcecode writer
FIXED      Updated all templates to generate a structure.xml
FIXED      Fatal error when a property, constant or method collection contains a null value
FIXED      Fixed several errors in the Old Ocean template
FIXED      Removed broken logging call from Xsl writer
FIXED      Several PHP notices
```

2013/06/09: Version 2.0.0b2
---------------------------

```
FIXED      Package could not be set for constants, resulting in fatal error
FIXED      Default template was still responsive and not responsive-twig
```

2013/06/09: Version 2.0.0b1
---------------------------

The 13th alpha of phpDocumentor contains the final functionality for version 2.0.
A lot of effort has been put in making sure that the API, Object structure and plugin mechanism will remain
backwards compatible until version 3.0.

Features have been changed, removed and added; making this an incredibly large backwards compatibility break compared
to 2.0.0a13.

> Important: if you have written your own XSL based template; please change the template.xml to generate the XML output
> first by adding a transformation with the XmlAst writer. Please look at our existing templates for an example.

> Important: please open an issue for every broken piece of functionality. We have taken the utmost care not to
> introduce bugs but due to large change some might occur.

In the following list we have tried to exhaustively document the list of changes and their impact. Due to the size of
the refactoring it is nigh impossible to make a 100% accurate list. Please contact us if you have questions; the website
http://www.phpdoc.org describes various methods.

```
ADDED      Twig Writer
ADDED      Xml Writer
ADDED      Router component to provide locations for various Url Schemes
ADDED      Responsive-twig template
ADDED      More tests
ADDED      Statistics are being logged to a log file (more will be logged there in the future)
ADDED      Compiler component that adds the ability to inject compiler passes at various points
ADDED      Linker to create object links between the various elements
ADDED      Indexes containing pre-generated lists to make the generation of templates faster
ADDED      More unit tests
FIXED      Define transformations in phpdoc.xml
FIXED      Define multiple templates in phpdoc.xml
FIXED      Performance is improved by re-approaching inheritance from a different angle
FIXED      Various small and unnamed bugs
CHANGED    Moved Inheritance from a Behaviour into the Descriptors
CHANGED    Temporarily removed deprecated report
CHANGED    Target option of parser now only accepts a folder
CHANGED    Various performance improvements
CHANGED    Doctrine Support is moved to its own Service Provider
CHANGED    Rewired dependencies and injection scheme to make better of the DIC
CHANGED    Object graph is written to various cache folders in a directory named 'build' by default
CHANGED    Cache is generated by Zend\Cache
CHANGED    Commands have been moved to their respective component
CHANGED    The Parser has been promoted to Service Provider
CHANGED    The Transformer has been promoted to Service Provider
CHANGED    Parse command has been prepared for internationalization
CHANGED    Template configuration is parsed using JmsSerializer
CHANGED    All logging is now PSR-3 compliant
CHANGED    Removed documentation for components that are still in flux
CHANGED    Updated documentation for existing functionality
DEPRECATED Behaviours are only executed for the XmlAst writer
BC-BREAK   Moved Validators to the Descriptor Builder and refactored for internationalization
BC-BREAK   Parser generates an object graph and not XML (structure.xml, use XmlAst writer now for XML output)
BC-BREAK   Replaced plugin system with Service Providers
```
