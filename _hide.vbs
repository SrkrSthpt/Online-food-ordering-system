Set shell = CreateObject("WScript.Shell")
If WScript.Arguments.Count = 0 Then WScript.Quit
Dim target, args, i
target = """" & WScript.Arguments(0) & """"
args = ""
For i = 1 To WScript.Arguments.Count - 1
    args = args & " " & WScript.Arguments(i)
Next
shell.Run "cmd /c " & target & args, 0, False
