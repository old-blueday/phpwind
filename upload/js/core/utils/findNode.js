/**
 *用途：因为非ie类浏览器会将文字和换行等元素当作一个节点来处理，导致遍历节点的时候存在很多兼容性问题。
	使用此方法统一忽略文字节点。
 *遍历节点方法；
 *使用举例：
 var a=getObj('nodeID');
 $n(a,0.1) 子节点
 $n(a,0.2) 子节点的子节点
 ....	   子节点的子节点的……
 $n(a,0.9) 最多支持0.9

 $n(a,-0.1)	 父节点
 $n(a,-0.2)	 父节点的父节点
 ....
 $n(a,-0.9) 最多支持-0.9

 $n(a,1)	 下一个节点
 $n(a,2)	 下一个节点的下一个节点
 ....

 $n(a,-1)	 上一个节点
 $n(a,-2)	 上一个节点的下一个节点
 ....

 综合使用：

 $n(a,0.3,1,0.2,-1)  子子子节点的下一个节点的子子节点的上一个节点

 注：由于存在语义的分歧或者无意义的表达，目前尚不支持诸如：$n(a,0.21)，$n(a,1.1)，$n(a,-2.3)这样的数值。
 */
function findNode(obj)
{
    var argu = [];
    for (var i = 1; i < arguments.length; i++)
    {
        argu.push(arguments[i]);
    }
    var n = obj;
    for (var i = 0; i < argu.length; i++)
    {
        if (argu[i] >= 1)
        {
            for (var j = 0; j < argu[i]; j++)
            {
                n = n.nextSibling;
                while (n && n.nodeType == 3)
                {
                    n = n.nextSibling;
                }
            }
        }
        if (argu[i] <= -1)
        {
            for (var j = 0; j < argu[i] * -1; j++)
            {
                n = n.previousSibling;
                while (n && n.nodeType == 3)
                {
                    n = n.previousSibling;
                }
            }
        }
        if ( - 1 < argu[i] && argu[i] < 0)
        {
            for (var j = 0; j > argu[i] * 10; j--)
            {
                n = n.parentNode;
            }
        }
        if (0 < argu[i] && argu[i] < 1)
        {
            for (var j = 0; j < argu[i] * 10; j++)
            {
                n.firstChild ? n = n.firstChild: 0;
                while (n && n.nodeType == 3)
                {
                    n = n.nextSibling;
                }
            }
        }
    }
    return  n;
};
$n=findNode;