The phpcs.xml standard contains 151 sniffs

DrupalPractice (24 sniffs)
--------------------------
* DrupalPractice.CodeAnalysis.VariableAnalysis
* DrupalPractice.Commenting.CommentEmptyLine
* DrupalPractice.FunctionCalls.CheckPlain
* DrupalPractice.FunctionCalls.CurlSslVerifier
* DrupalPractice.FunctionCalls.DbQuery
* DrupalPractice.FunctionCalls.DbSelectBraces
* DrupalPractice.FunctionCalls.DefaultValueSanitize
* DrupalPractice.FunctionCalls.FormErrorT
* DrupalPractice.FunctionCalls.LCheckPlain
* DrupalPractice.FunctionCalls.MessageT
* DrupalPractice.FunctionCalls.TCheckPlain
* DrupalPractice.FunctionCalls.Theme
* DrupalPractice.FunctionCalls.VariableSetSanitize
* DrupalPractice.FunctionDefinitions.AccessHookMenu
* DrupalPractice.FunctionDefinitions.FormAlterDoc
* DrupalPractice.FunctionDefinitions.HookInitCss
* DrupalPractice.FunctionDefinitions.InstallT
* DrupalPractice.General.AccessAdminPages
* DrupalPractice.General.ClassName
* DrupalPractice.General.DescriptionT
* DrupalPractice.General.FormStateInput
* DrupalPractice.General.LanguageNone
* DrupalPractice.General.OptionsT
* DrupalPractice.General.VariableName

DrupalSecure (12 sniffs)
------------------------
* DrupalSecure.CSRF.Query
* DrupalSecure.CodeExec.Unserialize
* DrupalSecure.General.Helper
* DrupalSecure.General.OutputTokens
* DrupalSecure.SQLi.Query
* DrupalSecure.XSS.CallbackOutput
* DrupalSecure.XSS.DrupalSetMessage
* DrupalSecure.XSS.DrupalSetTitle
* DrupalSecure.XSS.Get
* DrupalSecure.XSS.NodeTitle
* DrupalSecure.XSS.Output
* DrupalSecure.XSS.VariableGet

Drupal (58 sniffs)
------------------
* Drupal.Array.Array
* Drupal.CSS.ClassDefinitionNameSpacing
* Drupal.CSS.ColourDefinition
* Drupal.Classes.ClassCreateInstance
* Drupal.Classes.ClassDeclaration
* Drupal.Classes.FullyQualifiedNamespace
* Drupal.Classes.InterfaceName
* Drupal.Classes.UnusedUseStatement
* Drupal.Commenting.ClassComment
* Drupal.Commenting.DocComment
* Drupal.Commenting.DocCommentStar
* Drupal.Commenting.FileComment
* Drupal.Commenting.FunctionComment
* Drupal.Commenting.HookComment
* Drupal.Commenting.InlineComment
* Drupal.Commenting.PostStatementComment
* Drupal.ControlStructures.ControlSignature
* Drupal.ControlStructures.ElseIf
* Drupal.ControlStructures.InlineControlStructure
* Drupal.Files.EndFileNewline
* Drupal.Files.FileEncoding
* Drupal.Files.LineLength
* Drupal.Files.TxtFileLineLength
* Drupal.Formatting.MultiLineAssignment
* Drupal.Formatting.SpaceInlineIf
* Drupal.Formatting.SpaceUnaryOperator
* Drupal.Functions.DiscouragedFunctions
* Drupal.Functions.FunctionDeclaration
* Drupal.InfoFiles.AutoAddedKeys
* Drupal.InfoFiles.ClassFiles
* Drupal.InfoFiles.DuplicateEntry
* Drupal.InfoFiles.Required
* Drupal.NamingConventions.ValidClassName
* Drupal.NamingConventions.ValidFunctionName
* Drupal.NamingConventions.ValidGlobal
* Drupal.NamingConventions.ValidVariableName
* Drupal.Semantics.ConstantName
* Drupal.Semantics.EmptyInstall
* Drupal.Semantics.FunctionAlias
* Drupal.Semantics.FunctionT
* Drupal.Semantics.FunctionWatchdog
* Drupal.Semantics.InstallHooks
* Drupal.Semantics.LStringTranslatable
* Drupal.Semantics.PregSecurity
* Drupal.Semantics.RemoteAddress
* Drupal.Semantics.TInHookMenu
* Drupal.Semantics.TInHookSchema
* Drupal.Strings.UnnecessaryStringConcat
* Drupal.WhiteSpace.CloseBracketSpacing
* Drupal.WhiteSpace.Comma
* Drupal.WhiteSpace.EmptyLines
* Drupal.WhiteSpace.Namespace
* Drupal.WhiteSpace.ObjectOperatorIndent
* Drupal.WhiteSpace.ObjectOperatorSpacing
* Drupal.WhiteSpace.OpenBracketSpacing
* Drupal.WhiteSpace.OperatorSpacing
* Drupal.WhiteSpace.ScopeClosingBrace
* Drupal.WhiteSpace.ScopeIndent

Generic (11 sniffs)
-------------------
* Generic.Files.LineEndings
* Generic.Formatting.SpaceAfterCast
* Generic.Functions.FunctionCallArgumentSpacing
* Generic.Functions.OpeningFunctionBraceKernighanRitchie
* Generic.NamingConventions.ConstructorName
* Generic.NamingConventions.UpperCaseConstantName
* Generic.PHP.DeprecatedFunctions
* Generic.PHP.DisallowShortOpenTag
* Generic.PHP.LowerCaseKeyword
* Generic.PHP.UpperCaseConstant
* Generic.WhiteSpace.DisallowTabIndent

MySource (1 sniffs)
-------------------
* MySource.Debug.DebugCode

PEAR (3 sniffs)
---------------
* PEAR.Files.IncludingFile
* PEAR.Functions.FunctionCallSignature
* PEAR.Functions.ValidDefaultValue

PSR2 (2 sniffs)
---------------
* PSR2.Classes.PropertyDeclaration
* PSR2.Namespaces.UseDeclaration

QualityAssurance (15 sniffs)
----------------------------
* QualityAssurance.FeaturesFiles.DatestampFields
* QualityAssurance.FeaturesFiles.FieldNameLength
* QualityAssurance.FeaturesFiles.ForbiddenPermissions
* QualityAssurance.FeaturesFiles.LockedFields
* QualityAssurance.Functions.DrupalHttpRequest
* QualityAssurance.Functions.DrupalWrappers
* QualityAssurance.Functions.HardcodedImage
* QualityAssurance.Generic.HardcodedPath
* QualityAssurance.InfoFiles.Features
* QualityAssurance.InfoFiles.Forbidden
* QualityAssurance.InfoFiles.Required
* QualityAssurance.InstallFiles.FunctionDeclarations
* QualityAssurance.InstallFiles.HookUpdateN
* QualityAssurance.InstallFiles.InstallUpdateCallbacks
* QualityAssurance.InstallFiles.Update7000

Squiz (24 sniffs)
-----------------
* Squiz.Arrays.ArrayBracketSpacing
* Squiz.Arrays.ArrayDeclaration
* Squiz.CSS.ClassDefinitionClosingBraceSpace
* Squiz.CSS.ClassDefinitionOpeningBraceSpace
* Squiz.CSS.ColonSpacing
* Squiz.CSS.DisallowMultipleStyleDefinitions
* Squiz.CSS.EmptyClassDefinition
* Squiz.CSS.EmptyStyleDefinition
* Squiz.CSS.Indentation
* Squiz.CSS.MissingColon
* Squiz.CSS.SemicolonSpacing
* Squiz.Commenting.DocCommentAlignment
* Squiz.ControlStructures.ForEachLoopDeclaration
* Squiz.ControlStructures.ForLoopDeclaration
* Squiz.ControlStructures.SwitchDeclaration
* Squiz.Functions.FunctionDeclarationArgumentSpacing
* Squiz.PHP.LowercasePHPFunctions
* Squiz.PHP.NonExecutableCode
* Squiz.Scope.MethodScope
* Squiz.Strings.ConcatenationSpacing
* Squiz.WhiteSpace.FunctionSpacing
* Squiz.WhiteSpace.LanguageConstructSpacing
* Squiz.WhiteSpace.SemicolonSpacing
* Squiz.WhiteSpace.SuperfluousWhitespace

Zend (1 sniffs)
---------------
* Zend.Files.ClosingTag

