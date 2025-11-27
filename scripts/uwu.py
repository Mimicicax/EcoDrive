import os

assetsPath = "../assets"
whitespaces = "\n\t "
hexDigits = "0123456789abcdefABCDEF"
digits = "0123456789"

class Token:
	TOKEN_TYPE_WS = 0
	TOKEN_TYPE_STRING = 1
	TOKEN_TYPE_BAD_STRING = 2
	TOKEN_TYPE_OPEN_PAREN = 3
	TOKEN_TYPE_CLOSE_PAREN = 4
	TOKEN_TYPE_OPEN_SQUARE = 5
	TOKEN_TYPE_CLOSE_SQUARE = 6
	TOKEN_TYPE_OPEN_CURLY = 7
	TOKEN_TYPE_CLOSE_CURLY = 8
	TOKEN_TYPE_COLON = 9
	TOKEN_TYPE_SEMICOLON = 10
	TOKEN_TYPE_EOF = 11
	TOKEN_TYPE_DELIM = 12
	TOKEN_TYPE_NUMBER = 13
	TOKEN_TYPE_HASH = 14
	TOKEN_TYPE_COMMA = 15
	TOKEN_TYPE_DIMENSION = 16
	TOKEN_TYPE_PERCENTAGE = 17
	TOKEN_TYPE_CDC = 18
	TOKEN_TYPE_CDO = 19
	TOKEN_TYPE_AT_KEYWORD = 20
	TOKEN_TYPE_FUNCTION = 21
	TOKEN_TYPE_IDENT = 22
	TOKEN_TYPE_URL = 23
	TOKEN_TYPE_BAD_URL = 24


	tokenType = 0
	data = None
	subtype = None

	def __init__(self, type, data = None, subtype = None):
		self.tokenType = type
		self.data = data
		self.subtype = subtype

	def __str__(self):
		if self.tokenType == Token.TOKEN_TYPE_WS:
			return " "
		
		elif self.tokenType == Token.TOKEN_TYPE_STRING:
			return f"\"{self.data}\""
		
		elif self.tokenType == Token.TOKEN_TYPE_BAD_STRING or \
			 self.tokenType == Token.TOKEN_TYPE_EOF or \
			 self.tokenType == Token.TOKEN_TYPE_BAD_URL:
			return ""
		
		elif self.tokenType == Token.TOKEN_TYPE_OPEN_PAREN:
			return "("		
		
		elif self.tokenType == Token.TOKEN_TYPE_CLOSE_PAREN:
			return ")"
		
		elif self.tokenType == Token.TOKEN_TYPE_OPEN_SQUARE:
			return "["		
		
		elif self.tokenType == Token.TOKEN_TYPE_CLOSE_SQUARE:
			return "]"	
		
		elif self.tokenType == Token.TOKEN_TYPE_OPEN_CURLY:
			return "{"		
		
		elif self.tokenType == Token.TOKEN_TYPE_CLOSE_CURLY:
			return "}"	
		
		elif self.tokenType == Token.TOKEN_TYPE_COLON:
			return ":"		
		
		elif self.tokenType == Token.TOKEN_TYPE_SEMICOLON:
			return ";"	
				
		elif self.tokenType == Token.TOKEN_TYPE_COMMA:
			return ","	
		
		elif self.tokenType == Token.TOKEN_TYPE_DELIM or \
			 self.tokenType == Token.TOKEN_TYPE_NUMBER or \
			 self.tokenType == Token.TOKEN_TYPE_DIMENSION or \
			 self.tokenType == Token.TOKEN_TYPE_IDENT:
			return self.data	

		elif self.tokenType == Token.TOKEN_TYPE_HASH:
			return f"#{self.data}"
				
		elif self.tokenType == Token.TOKEN_TYPE_PERCENTAGE:
			return f"{self.data}%"
					
		elif self.tokenType == Token.TOKEN_TYPE_CDC:
			return "-->"
							
		elif self.tokenType == Token.TOKEN_TYPE_CDO:
			return "<!--"
									
		elif self.tokenType == Token.TOKEN_TYPE_AT_KEYWORD:
			return f"@{self.data}"
											
		elif self.tokenType == Token.TOKEN_TYPE_FUNCTION:
			return f"{self.data}("	
		
		elif self.tokenType == Token.TOKEN_TYPE_URL:
			return f"url(\"{self.data}\")"
		
# https://www.w3.org/TR/css-syntax-3/#ident-start-code-point
def isIdentStartChar(ch):
	return ch is not None and (ch.isalpha() or not ch.isascii() or ch == '_')

# https://www.w3.org/TR/css-syntax-3/#ident-code-point
def isIdentChar(ch):
	return ch is not None and (isIdentStartChar(ch) or ch in digits or ch == '-')

# https://www.w3.org/TR/css-syntax-3/#non-printable-code-point
def isNonPrintableChar(ch):
	return ch.isascii() and ((0 <= ord(ch) and ord(ch) <= 0x008) or (0x000e <= ord(ch) and ord(ch) <= 0x001f) or ord(ch) == 0x007f or ord(ch) == 0x000b)

# https://www.w3.org/TR/css-syntax-3/#check-if-two-code-points-are-a-valid-escape
def startsValidEscape(content, ptr):
	ch1 = content[ptr]
	ch2 = content[ptr + 1] if ptr < len(content) - 1 else None

	if ch1 != '\\' or ch2 == '\n':
		return False
	
	return True

# https://www.w3.org/TR/css-syntax-3/#check-if-three-code-points-would-start-an-ident-sequence
def startsIdentSequence(content, ptr):
	ch1 = content[ptr]
	ch2 = content[ptr + 1] if ptr < len(content) - 1 else None

	if isIdentStartChar(ch1):
		return True
	
	elif ch1 == '-':
		if isIdentStartChar(ch2) or ch2 == '-' or startsValidEscape(content, ptr + 1):
			return True
		
		return False
	
	elif ch1 == '\\':
		return startsValidEscape(content, ptr)
	
	else:
		return False

# https://www.w3.org/TR/css-syntax-3/#check-if-three-code-points-would-start-a-number
def startsNumber(content, ptr):
	ch1 = content[ptr]
	ch2 = content[ptr + 1] if ptr < len(content) - 1 else None
	ch3 = content[ptr + 2] if ptr < len(content) - 2 else None

	if ch1 == '+' or ch1 == '-':
		if ch2 is not None and ch2 in digits:
			return True
		
		elif ch2 == '.' and ch3 is not None and ch3 in digits:
			return True
		
		return False
	
	elif ch1 == '.':
		return ch2 is not None and ch2 in digits
	
	else:
		return ch1 in digits

# https://www.w3.org/TR/css-syntax-3/#input-preprocessing
def preprocess(content):
	result = ""

	for i in range(0, len(content)):
		if content[i] == u'\u000c':
			result += u'\u000a'

		elif content[i] == u'\u000d':
			if i == len(content) - 1 or content[i + 1] != u'\u000a':
				result += u'\u000a'

			elif content[i + 1] == u'\u000a':
				result += u'\u000a'
				i += 1

		else:
			result += content[i]

	return result
 
# https://www.w3.org/TR/css-syntax-3/#consume-comments
def consumeComment(content, ptr):
	if ptr == len(content):
		return ptr
	
	if content[ptr] == '/' and ptr != len(content) - 1 and content[ptr + 1] == '*':
		ptr += 2

		while ptr < len(content) - 1:
			if content[ptr] == '*' and content[ptr + 1] == '/':
				ptr += 2
				break

			ptr += 1

	return ptr

def consumeWhitespace(content, ptr):
	while ptr < len(content) and content[ptr] in whitespaces:
		ptr += 1

	return (ptr, Token(Token.TOKEN_TYPE_WS))

# https://www.w3.org/TR/css-syntax-3/#consume-an-escaped-code-point
def consumeEscaped(content, ptr):
	escaped = "\\"

	if content[ptr] in hexDigits:
		count = 0

		while ptr < len(content) and count < 5 and content[ptr] in hexDigits:
			escaped	+= content[ptr]
			ptr += 1
			count += 1

		if ptr != len(content) and content[ptr] in whitespaces:
			ptr += 1
		
	else:
		escaped	+= content[ptr]
		ptr += 1

	return (ptr, escaped)

# https://www.w3.org/TR/css-syntax-3/#consume-a-string-token
def consumeString(content, ptr, ending = None):
	if (ending == None):
		ending = content[ptr]
		ptr += 1

	string = ""

	while ptr != len(content):
		if content[ptr] == ending:
			return (ptr + 1, Token(Token.TOKEN_TYPE_STRING, string))
		
		elif content[ptr] == '\n':
			return (ptr, Token(Token.TOKEN_TYPE_BAD_STRING))

		elif content[ptr] == '\\':
			ptr += 1

			if ptr == len(content):
				return (ptr, Token(Token.TOKEN_TYPE_STRING, string))
			
			elif ptr == '\n':
				ptr += 1

			else:
				(ptr, escaped) = consumeEscaped(content, ptr)
				string += escaped
				
		else:
			string += content[ptr]
			ptr += 1

	return (ptr, Token(Token.TOKEN_TYPE_STRING, string))

# https://www.w3.org/TR/css-syntax-3/#consume-an-ident-sequence
def consumeIdentSequence(content, ptr):
	result = ""

	while ptr < len(content):
		if isIdentChar(content[ptr]):
			result += content[ptr]

		elif startsValidEscape(content, ptr):
			ptr += 1
			(ptr, escaped) = consumeEscaped(content, ptr)
			result += escaped
			continue

		else:
			break

		ptr += 1
	
	return (ptr, result)

# https://www.w3.org/TR/css-syntax-3/#consume-number
def consumeNumber(content, ptr):
	repr = ""

	if content[ptr] == '+' or content[ptr] == '-':
		repr += content[ptr]
		ptr += 1

	while ptr < len(content) and content[ptr] in digits:
		repr += content[ptr]
		ptr += 1

	if ptr < len(content) - 1 and content[ptr] == '.' and content[ptr + 1] in digits:
		repr += "." + content[ptr + 1]
		ptr += 2

		while ptr < len(content) and content[ptr] in digits:
			repr += content[ptr]
			ptr += 1

	if ptr < len(content) - 1 and (content[ptr] == 'e' or content[ptr] == 'E'):
		if (content[ptr + 1] == '-' or content[ptr + 1] == '+') and ptr < len(content) - 2 and content[ptr + 2] in digits:
			repr += content[ptr] + content[ptr + 1]
			ptr += 2

		elif content[ptr + 1] in digits:
			repr += content[ptr]
			ptr += 1

		while ptr < len(content) and content[ptr] in digits:
			repr += content[ptr]
			ptr += 1

	return (ptr, Token(Token.TOKEN_TYPE_NUMBER, repr))

# https://www.w3.org/TR/css-syntax-3/#consume-a-numeric-token
def consumeNumeric(content, ptr):
	(ptr, repr) = consumeNumber(content, ptr)

	if startsIdentSequence(content, ptr):
		(ptr, dim) = consumeIdentSequence(content, ptr)
		return (ptr, Token(Token.TOKEN_TYPE_DIMENSION, repr.data + dim))

	elif ptr < len(content) and content[ptr] == '%':
		return (ptr + 1, Token(Token.TOKEN_TYPE_PERCENTAGE, repr.data))

	else:
		return (ptr, Token(Token.TOKEN_TYPE_NUMBER, repr.data))

def consumeBadURL(content, ptr):
	while ptr < len(content):
		if content[ptr] == ')':
			break

		elif startsValidEscape(content, ptr):
			(ptr, _) = consumeEscaped(content, ptr)

		else:
			ptr += 1

	return (ptr, Token(Token.TOKEN_TYPE_BAD_URL))

# https://www.w3.org/TR/css-syntax-3/#consume-a-url-token
def consumeURL(content, ptr):
	while ptr < len(content) and content[ptr] in whitespaces:
		ptr += 1
				
	value = ""

	while ptr < len(content):
		if content[ptr] == ')':
			return (ptr + 1, Token(Token.TOKEN_TYPE_URL, value))
		
		elif content[ptr] in whitespaces:
			while ptr < len(content) and content[ptr] in whitespaces:
				ptr += 1

			if ptr == len(content) or content[ptr] == ')':
				if ptr != len(content): ptr += 1
				return (ptr, Token(Token.TOKEN_TYPE_URL, value))

			else:
				return consumeBadURL(content, ptr)

		elif content[ptr] == '\"' or content[ptr] == '\'' or content[ptr] == '(' or isNonPrintableChar(content[ptr]):
			return consumeBadURL(content, ptr)

		elif content[ptr] == '\\':
			if startsValidEscape(content, ptr):
				(ptr, esc) = consumeEscaped(content, ptr)
				value += esc

			else:
				return consumeBadURL(content, ptr)
			
		else:
			value += content[ptr]

		ptr += 1
		
	return (ptr, Token(Token.TOKEN_TYPE_URL, value))

# https://www.w3.org/TR/css-syntax-3/#consume-an-ident-like-token
def consumeIdentLike(content, ptr):
	(ptr, ident) = consumeIdentSequence(content, ptr)

	if ident.lower() == "url" and ptr < len(content) and content[ptr] == '(':
		ptr += 1

		while ptr < len(content) - 1 and content[ptr] in whitespaces and content[ptr + 1] in whitespaces:
			ptr += 1

		if content[ptr] == '\"' or content[ptr] == '\'' or \
		   (ptr < len(content) - 1 and content[ptr] in whitespaces and (content[ptr + 1] == '\"' or content[ptr + 1] == '\'')):
			return (ptr, Token(Token.TOKEN_TYPE_FUNCTION, ident))
		
		else:
			return consumeURL(content, ptr)
		
	elif ptr < len(content) and content[ptr] == '(':
		ptr += 1
		return (ptr, Token(Token.TOKEN_TYPE_FUNCTION, ident))
	
	else:
		return (ptr, Token(Token.TOKEN_TYPE_IDENT, ident))

# https://www.w3.org/TR/css-syntax-3/#consume-token
def consumeToken(content, ptr):
	ptr = consumeComment(content, ptr)

	if ptr == len(content):
		return (ptr, Token(Token.TOKEN_TYPE_EOF))
	
	ch = content[ptr]

	if ch in whitespaces:
		return consumeWhitespace(content, ptr)

	elif ch == "\"" or ch == '\'':
		return consumeString(content, ptr)
	
	elif ch == '#':
		ptr += 1
		
		if isIdentChar(content[ptr]) or startsValidEscape(content, ptr):
			subtype = "id" if startsIdentSequence(content, ptr) else None
			(ptr, value) = consumeIdentSequence(content, ptr)

			return (ptr, Token(Token.TOKEN_TYPE_HASH, value, subtype))

		else:
			return (ptr, Token(Token.TOKEN_TYPE_DELIM, '#'))

	elif ch == '+':
		if startsNumber(content, ptr):
			return consumeNumeric(content, ptr)
		
		else:
			return (ptr + 1, Token(Token.TOKEN_TYPE_DELIM, '+'))

	elif ch == '-':
		if startsNumber(content, ptr):
			return consumeNumeric(content, ptr)
		
		elif ptr < len(content) - 2 and content[ptr + 1] == '-' and content[ptr + 2] == '>':
			ptr += 3
			return (ptr, Token(Token.TOKEN_TYPE_CDC))
		
		elif startsIdentSequence(content, ptr):
			return consumeIdentLike(content, ptr)
		
		else:
			return (ptr + 1, Token(Token.TOKEN_TYPE_DELIM, '-'))

	elif ch == '.':
		if startsNumber(content, ptr):
			return consumeNumeric(content, ptr)

		else:
			return (ptr + 1, Token(Token.TOKEN_TYPE_DELIM, '.'))

	elif ch == '<':
		if content[slice(ptr, len(content))].startswith("<!--"):
			return (ptr + 4, Token(Token.TOKEN_TYPE_CDO))
		
		else:
			return (ptr + 1, Token(Token.TOKEN_TYPE_DELIM, '<'))

	elif ch == '@':
		ptr += 1

		if startsIdentSequence(content, ptr):
			(ptr, ident) = consumeIdentSequence(content, ptr)
			return (ptr, Token(Token.TOKEN_TYPE_AT_KEYWORD, ident))
		
		else:
			return (ptr, Token(Token.TOKEN_TYPE_DELIM, '@'))

	elif ch == '\\':
		if startsValidEscape(content, ptr):
			return consumeIdentLike(content, ptr)
		
		else:
			return (ptr + 1, Token(Token.TOKEN_TYPE_DELIM, '\\'))

	elif ch in digits:
		return consumeNumeric(content, ptr)
	
	elif isIdentStartChar(ch):
		return consumeIdentLike(content, ptr)

	elif ch == '{':
		return (ptr + 1, Token(Token.TOKEN_TYPE_OPEN_CURLY))
	
	elif ch == '}':
		return (ptr + 1, Token(Token.TOKEN_TYPE_CLOSE_CURLY))
	
	elif ch == '[':
		return (ptr + 1, Token(Token.TOKEN_TYPE_OPEN_SQUARE))
	
	elif ch == ']':
		return (ptr + 1, Token(Token.TOKEN_TYPE_CLOSE_SQUARE))
	
	elif ch == '(':
		return (ptr + 1, Token(Token.TOKEN_TYPE_OPEN_PAREN))
	
	elif ch == ')':
		return (ptr + 1, Token(Token.TOKEN_TYPE_CLOSE_PAREN))	

	elif ch == ',':
		return (ptr + 1, Token(Token.TOKEN_TYPE_COMMA))	
	
	elif ch == ':':
		return (ptr + 1, Token(Token.TOKEN_TYPE_COLON))	
	
	elif ch == ';':
		return (ptr + 1, Token(Token.TOKEN_TYPE_SEMICOLON))
	
	else:
		return (ptr + 1, Token(Token.TOKEN_TYPE_DELIM, content[ptr]))

variableMap = {}

def parseCSS(content):
	content = preprocess(content)
	pos = 0
	tokenList = []
	symbolCount = len(variableMap)

	while True:
		(pos, token) = consumeToken(content, pos)

		if token.tokenType == Token.TOKEN_TYPE_EOF:
			break

		else:
			if token.tokenType == Token.TOKEN_TYPE_IDENT and token.data.startswith("--"):

				if token.data in variableMap:
					token.data = variableMap[token.data]

				else:
					variableMap[token.data] = "--" + str(symbolCount + 1)
					token.data = "--" + str(symbolCount + 1)
					symbolCount += 1
			
			tokenList.append(token)

	return tokenList

output = ""

for file in os.listdir(os.fsencode(assetsPath + "/css")):
	path = os.fsdecode(file)

	if not path.endswith(".css"):
		continue

	with open(assetsPath + "/css/" + path, "r") as css:
		content = preprocess(css.read())
		tokens = parseCSS(content)

		for t in tokens:
			output += str(t)

with open(assetsPath + "/style.css", "w") as outFile:
	outFile.write(output)

print("A stílusok kombinálva lettek a style.css fájlba UwU")