/* misc includes */
#include <iostream>
#include <string.h>
#include <vector>
#include <map>
#include <stdlib.h>
#include <locale>
#include <time.h>
#include <sstream>
#include "/usr/include/php5/ext/standard/file.h"
#include "/usr/include/php5/main/php_streams.h"

/* save some typing later */
using namespace std;

/* list of upper case characters */
char uchar_list_ [26] = {'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L',
    'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'};

/* 256 strings to be used for a simple substitution cipher */
string char_list_ [256] = {"a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
    "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z", "Aa",
    "Ab", "Ac", "Ad", "Ae", "Af", "Ag", "Ah", "Ai", "Aj", "Ak", "Al", "Am", "An", "Ao",
    "Ap", "Aq", "Ar", "As", "At", "Au", "Av", "Aw", "Ax", "Ay", "Az", "Ba", "Bb", "Bc",
    "Bd", "Be", "Bf", "Bg", "Bh", "Bi", "Bj", "Bk", "Bl", "Bm", "Bn", "Bo", "Bp", "Bq",
    "Br", "Bs", "Bt", "Bu", "Bv", "Bw", "Bx", "By", "Bz", "Ca", "Cb", "Cc", "Cd", "Ce",
    "Cf", "Cg", "Ch", "Ci", "Cj", "Ck", "Cl", "Cm", "Cn", "Co", "Cp", "Cq", "Cr", "Cs",
    "Ct", "Cu", "Cv", "Cw", "Cx", "Cy", "Cz", "Da", "Db", "Dc", "Dd", "De", "Df", "Dg",
    "Dh", "Di", "Dj", "Dk", "Dl", "Dm", "Dn", "Do", "Dp", "Dq", "Dr", "Ds", "Dt", "Du",
    "Dv", "Dw", "Dx", "Dy", "Dz", "Ea", "Eb", "Ec", "Ed", "Ee", "Ef", "Eg", "Eh", "Ei",
    "Ej", "Ek", "El", "Em", "En", "Eo", "Ep", "Eq", "Er", "Es", "Et", "Eu", "Ev", "Ew",
    "Ex", "Ey", "Ez", "Fa", "Fb", "Fc", "Fd", "Fe", "Ff", "Fg", "Fh", "Fi", "Fj", "Fk",
    "Fl", "Fm", "Fn", "Fo", "Fp", "Fq", "Fr", "Fs", "Ft", "Fu", "Fv", "Fw", "Fx", "Fy",
    "Fz", "Ga", "Gb", "Gc", "Gd", "Ge", "Gf", "Gg", "Gh", "Gi", "Gj", "Gk", "Gl", "Gm",
    "Gn", "Go", "Gp", "Gq", "Gr", "Gs", "Gt", "Gu", "Gv", "Gw", "Gx", "Gy", "Gz", "Pa",
    "Pb", "Pc", "Pd", "Pe", "Pf", "Pg", "Ph", "Pi", "Pj", "Pk", "Pl", "Pm", "Pn", "Po",
    "Pp", "Pq", "Pr", "Ps", "Pt", "Pu", "Pv", "Pw", "Px", "Py", "Pz", "Qa", "Qb", "Qc",
    "Qd", "Qe", "Qf", "Qg", "Qh", "Qi", "Qj", "Qk", "Ql", "Qm", "Qn", "Qo", "Qp", "Qq",
    "Qr", "Qs", "Qt", "Qu", "Qv" };

/* define our utils class */
class HastymailUtils {
    public:
        /* each of these maps directly to a function callable from PHP and defined in hm_utils.cpp */
        int   hm_html_strlen(const string str);
        char* hm_html_trim(const string str, int len);
        char* hm_html_entities(string str);
        char* hm_crypt(const string str, const string key);
        char* hm_decrypt(const string str, const string key);
        char* hm_utf8_to_html(const string input);
        void  hm_parse_imap_line(string line, long current_size, long max, zval* handle, zval* return_value);
    private:
        string hm_crypt_string(const string str, const string key);
        string get_quoted_string(string str);
        string hm_decode(const string str);
        string& hm_replace_all(string& str, const string& from, const string& to);
        string hm_code(const string str);
        string hm_read_literal(int size, int max, int current, php_stream* handle);
        char* get_non_const_str(const string& str);
        int search_char_list(string str);
        int search_uchar_list(char chr);
};

/* convert a const string to a non const string for returning via the php module API */
char* HastymailUtils::get_non_const_str(const string& str) {
    static vector<char> var;
    var.assign(str.begin(),str.end());
    var.push_back('\0');
    return &var[0];
}

/* public method to get the string length of a string with embedded HTML entities */
int HastymailUtils::hm_html_strlen(const string str) {
    int len = 0;
    int entity_len = 0;
    for (int i = 0; str[i]; i++) {
        if (entity_len > 0) {
            if (str[i] == ';') {
                len -= entity_len;
                entity_len = 0;
            }
            else {
                entity_len++;
            }
        }
        if (str[i] == '&') {
            entity_len = 1;
        }
        len++;
    }
    return len;
}

/* public method to trim a string to certian size without breaking HTML entities */
char* HastymailUtils::hm_html_trim(const string str, int len) {
    int count = 0;
    int entity_len = 0;
    string substr;
    for (int i = 0; str[i]; i++) {
        if (entity_len > 0) {
            if (str[i] == ';') {
                entity_len++;
                count -= entity_len;
                entity_len = 0;
            }
            else {
                entity_len++;
            }
        }
        if (str[i] == '&') {
            entity_len = 1;
        }
        if (entity_len == 0 && count == len) {
            return get_non_const_str(str.substr(0, i));
        }
        count++;
    }
    return get_non_const_str(str);
}

/* replace all occurences of a substring in a string */
string& HastymailUtils::hm_replace_all(string& str, const string& from, const string& to) {
    size_t index = 0;
    size_t found;
    while ((found = str.find(from, index)) != string::npos) {
        str.replace(found, from.size(), to);
        index = found + to.size();
    }
    return str;
}

/* public method to replace '"<> and & characters with HTML entities */
char* HastymailUtils::hm_html_entities(string str) {
    return get_non_const_str(hm_replace_all(hm_replace_all(hm_replace_all(hm_replace_all(
        hm_replace_all(str, "\"", "&#034;"), "'", "&#039;"), "& ", "&amp; "), "<", "&lt;"), ">", "&gt;"));
}

/* RC4 encryption routine */
string HastymailUtils::hm_crypt_string(const string str, const string key) {
    string enc_string;
    int key_size = key.size();
    int str_size = str.size();
    int ord_list [key_size];
    int s [key_size];
    int i = 0;
    int f = 0;
    int tmp;
    int t;
    int last;
    for (int k = 0; k < key_size; k++) {
        ord_list[k] = int(key[k]);
        s[k] = k;
    }
    for (int n = 0; n < key_size; n++) {
        f = ((f + s[n] + ord_list[n]) % key_size);
        tmp = s[n];
        s[n] = s[f];
        s[f] = tmp;
    }
    i = 0;
    f = 0;
    for (int l=0; l < str_size; l++) {
        i = ((i + 1) % key_size);
        f = ((f + s[i]) % key_size);
        tmp = s[i];
        s[i] = s[f];
        s[f] = tmp;
        t = s[i] + s[f];
        last = (t^int(str[l]));
        i++;
        f++;
        enc_string.push_back(char(last));
    }
    return get_non_const_str(enc_string);
}

/* public method to encrypt a string */
char * HastymailUtils::hm_crypt(const string str, const string key) {
    time_t seconds = time(NULL);
    char chrtime [20];
    sprintf(chrtime, "%d", seconds);
    string strtime = chrtime;
    return get_non_const_str(hm_code(hm_crypt_string(strtime+str, key)));
}

/* public method to decrypt a string */
char * HastymailUtils::hm_decrypt(const string str, const string key) {
    return get_non_const_str(hm_crypt_string(hm_decode(str), key));
}

/* encode a encrypted string to have only a-zA-Z */
string HastymailUtils::hm_code(const string str) {
    int str_size = str.size();
    unsigned char chr;
    unsigned int ord;
    string res = "";
    for (int i=0; i<str_size;i++) {
        chr = str[i];
        ord = chr;
        res += char_list_[ord];
    }
    return res;
}

/* search the list of upper case chars uchar_list_ for a match */
int HastymailUtils::search_uchar_list(char chr) {
    for (int i=0; i<26; i++) {
        if (chr == uchar_list_[i]) {
            return i;
        } 
    }
    return -1;
}

/* search the list of strings char_list_ for a match */
int HastymailUtils::search_char_list(string str) {
    for (int i=0; i<256; i++) {
        if (str == char_list_[i]) {
            return i;
        } 
    }
    return -1;
}

/* undo the cipher code to convert an ASCII string back to its raw state */
string HastymailUtils::hm_decode(const string str) {
    int str_size = str.size();
    int ord;
    stringstream results;
    string test;
    for (int i=0; i<str_size;i++) {
        
        if (search_uchar_list(str[i]) != -1) {
            test = str[i];
            test += str[(i + 1)];
            i++;
        }
        else {
            test = str[i];
        }
        ord = search_char_list(test);
        results << char(ord);
    }
    return results.str();
}

/* public method to convert raw utf8 to HTML entities for display */
char * HastymailUtils::hm_utf8_to_html(const string input) {
    stringstream output;
    int len = input.length();
    int num;
    unsigned int ord;
    unsigned int ord1;
    unsigned int ord2;
    unsigned int ord3;
    unsigned int ord4;
    unsigned int ord5;
    map<int,string> cchars;
    cchars[128] = "160";
    cchars[129] = "160";
    cchars[130] = "8218";
    cchars[131] = "402";
    cchars[132] = "8222";
    cchars[133] = "8230";
    cchars[134] = "8224";
    cchars[135] = "8225";
    cchars[136] = "710";
    cchars[137] = "8240";
    cchars[138] = "352";
    cchars[139] = "8249";
    cchars[140] = "338";
    cchars[141] = "160";
    cchars[142] = "160";
    cchars[143] = "160";
    cchars[144] = "160";
    cchars[145] = "8216";
    cchars[146] = "8217";
    cchars[147] = "8220";
    cchars[148] = "8221";
    cchars[149] = "8226";
    cchars[150] = "8211";
    cchars[151] = "8212";
    cchars[152] = "732";
    cchars[153] = "8482";
    cchars[154] = "353";
    cchars[155] = "8250";
    cchars[156] = "339";
    cchars[157] = "160";
    cchars[158] = "160";
    cchars[159] = "376";
    for (int i=0; i<len;i++) {
        num = 0;
        ord = (unsigned char)input[i];
        if (ord == 10 || ord == 13 || ord == 9) {
            output << input[i];
        }
        else if (ord < 32) {
            output << '?';
        }
        else if (ord < 128) {
            output << input[i];
        }
        else if (ord < 224) {
            ord1 = (unsigned char)input[(i + 1)];
            num = (((ord % 32) * 64) +
                  (ord1 % 64));
            i += 1;
        }
        else if (ord < 240) {
            ord1 = (unsigned char)input[(i + 1)];
            ord2 = (unsigned char)input[(i + 2)];
            num = (((ord % 16) * 4096) +
                  ((ord1 % 64) * 64) +
                  (ord2 % 64));
            i += 2;
        }
        else if (ord < 248) {
            ord1 = (unsigned char)input[(i + 1)];
            ord2 = (unsigned char)input[(i + 2)];
            ord3 = (unsigned char)input[(i + 3)];
            num = (((ord % 8) * 262144) +
                  ((ord1 % 64) * 4096) +
                  ((ord2 % 64) * 64) +
                  (ord3 % 64));
            i += 3;
        }
        else if (ord < 252) {
            ord1 = (unsigned char)input[(i + 1)];
            ord2 = (unsigned char)input[(i + 2)];
            ord3 = (unsigned char)input[(i + 3)];
            ord4 = (unsigned char)input[(i + 4)];
            num = (((ord % 4) * 16777216) +
                  ((ord1 % 64) * 262144) +
                  ((ord2 % 64) * 4096) +
                  ((ord3 % 64) * 64) +
                  (ord4 % 64));
            i += 4;
        }
        else {
            ord1 = (unsigned char)input[(i + 1)];
            ord2 = (unsigned char)input[(i + 2)];
            ord3 = (unsigned char)input[(i + 3)];
            ord4 = (unsigned char)input[(i + 4)];
            ord5 = (unsigned char)input[(i + 5)];
            num = ((ord % 2) * 1073741824 +
                  ((ord1 % 4) * 16777216) +
                  ((ord2 % 64) * 262144) +
                  ((ord3 % 64) * 4096) +
                  ((ord4 % 64) * 64) +
                  (ord5 % 64));
            i += 5;
        }
        if (num != 0) {
            if (num > 127 && num < 160) {
                output << "&#" << cchars[num] << ";";
            }
            else {
                output << "&#" << num << ";";
            }
        }
    }
    return get_non_const_str(output.str());
}

/* get a quoted string and handle embedded quotes */
string HastymailUtils::get_quoted_string(string str) {
    int end_range = 0;
    int size = str.size();
    bool escaped = false;
    string res;
    for (int i=0;i<size;i++) {
        if (str[i] == '\\') {
            escaped = true;
        } 
        else if (str[i] == '"' && escaped) {
            escaped = false;
        }
        else if (i != 0 && !escaped && str[i] == '"') {
            end_range = i;
            break; 
        }
    }
    if (end_range != 0) {
        res = str.substr(1, (end_range -1));
    }
    else {
        res = str;
    }
    return res;
}

/* public method to parse a "line" of IMAP output */
void HastymailUtils::hm_parse_imap_line(string line, long current_size, long max, zval* handle, zval* return_value) {
    array_init(return_value);
    bool line_cont = false;
    char chr_list [3] = {' ', ')', ']'};
    int marker;
    int marker_tmp = -1;
    int c;
    int lit_marker;
    int lit_size;
    int str_size = line.size();
    int i;
    string lit_size_str;
    string char_check;
    string chunk;
    string temp_chunk;
    zval* mysubarray;
    php_stream *stream = NULL;
    php_stream_from_zval(stream, &handle)
    ALLOC_INIT_ZVAL(mysubarray);
    array_init(mysubarray);
    for (i=0;i<str_size;i++) {
        chunk = "";
        if (line[i] == '\r' || line[i] == '\n') {
            break;
        }
        else if (line[i] == ' ') {
            continue;
        }
        else if (line[i] == '*' || line[i] == '[' || line[i] == ']' || line[i] == '(' || line[i] == ')') {
            chunk.push_back(line[i]);
        }
        else if (line[i] == '"') {
            chunk = get_quoted_string(line.substr(i, (str_size - i)));
            i += chunk.size() + 1;
        }
        else if (line[i] == '{') {
            lit_marker = line.find('}', i);
            if (lit_marker > -1) {
                lit_size_str = line.substr((i + 1), (lit_marker - i - 1));
                lit_size = atoi(lit_size_str.c_str());
                i += lit_size_str.size() + 1;
                if (lit_size > 0) {
                    chunk = hm_read_literal(lit_size, max, current_size, stream);
                    line_cont = true;
                }
            }
        }
        else {
            marker = -1;
            for (c = 0; c < 3; c++) {
                marker_tmp = line.find(chr_list[c], i);    
                if (marker_tmp != string::npos) {
                    if (marker ==  -1 || marker_tmp < marker) {
                        marker = marker_tmp;
                    }
                } 
            }
            if (marker != string::npos) {
                chunk = line.substr(i, (marker - i));
            }
            else {
                chunk = line.substr(i, (str_size - i - 2));
            }
            i += (chunk.size() - 1);
        }
        if (chunk.size() > 0) {
            add_next_index_string(mysubarray, chunk.c_str(), 1);
        }
    }
    add_next_index_bool(return_value, line_cont);
    add_assoc_zval(return_value, "1", mysubarray);
}

/* read in an IMAP literal from the IMAP stream socket */
string HastymailUtils::hm_read_literal(int size, int max, int current, php_stream* handle) {
    char *contents;
    char *leftover;
    int maxed = 0;
    string res;
    stringstream tmp;
    int max_read = size;
    if (max > 0 && (current + size) > max) {
        max_read = max - current;
        maxed = size - max_read;
    }
    php_stream_copy_to_mem(handle, &contents, max_read, 0);
    if (maxed != 0) {
        php_stream_copy_to_mem(handle, &leftover, maxed, 0);
    }
    res = contents;
    return res;
}
