"use client"

import * as React from "react"
import { cn } from "@/lib/utils"

interface DropdownMenuContextType {
  open: boolean
  onOpenChange: (open: boolean) => void
}

const DropdownMenuContext = React.createContext<DropdownMenuContextType | undefined>(undefined)

interface DropdownMenuProps {
  children: React.ReactNode
  open?: boolean
  onOpenChange?: (open: boolean) => void
}

const DropdownMenu: React.FC<DropdownMenuProps> = ({ children, open = false, onOpenChange }) => {
  const [isOpen, setIsOpen] = React.useState(open)
  
  React.useEffect(() => {
    setIsOpen(open)
  }, [open])
  
  const handleOpenChange = (newOpen: boolean) => {
    setIsOpen(newOpen)
    onOpenChange?.(newOpen)
  }
  
  return (
    <DropdownMenuContext.Provider value={{ open: isOpen, onOpenChange: handleOpenChange }}>
      {children}
    </DropdownMenuContext.Provider>
  )
}

const DropdownMenuTrigger = React.forwardRef<
  HTMLButtonElement,
  React.ButtonHTMLAttributes<HTMLButtonElement>
>(({ className, children, ...props }, ref) => {
  const context = React.useContext(DropdownMenuContext)
  
  return (
    <button
      ref={ref}
      className={className}
      onClick={() => context?.onOpenChange(!context.open)}
      {...props}
    >
      {children}
    </button>
  )
})
DropdownMenuTrigger.displayName = "DropdownMenuTrigger"

interface DropdownMenuContentProps extends React.HTMLAttributes<HTMLDivElement> {
  children: React.ReactNode
}

const DropdownMenuContent = React.forwardRef<HTMLDivElement, DropdownMenuContentProps>(
  ({ className, children, ...props }, ref) => {
    const context = React.useContext(DropdownMenuContext)
    
    if (!context?.open) return null
    
    return (
      <div
        ref={ref}
        className={cn("dropdown-menu show border shadow position-absolute", className)}
        {...props}
      >
        {children}
      </div>
    )
  }
)
DropdownMenuContent.displayName = "DropdownMenuContent"

const DropdownMenuItem = React.forwardRef<
  HTMLButtonElement,
  React.ButtonHTMLAttributes<HTMLButtonElement> & { inset?: boolean }
>(({ className, inset, children, ...props }, ref) => {
  const context = React.useContext(DropdownMenuContext)
  
  return (
    <button
      ref={ref}
      className={cn(
        "dropdown-item d-flex align-items-center gap-2 border-0 bg-transparent w-100 text-start",
        inset && "ps-4",
        className
      )}
      onClick={(e) => {
        props.onClick?.(e)
        context?.onOpenChange(false)
      }}
      {...props}
    >
      {children}
    </button>
  )
})
DropdownMenuItem.displayName = "DropdownMenuItem"

const DropdownMenuLabel = React.forwardRef<
  HTMLDivElement,
  React.HTMLAttributes<HTMLDivElement> & { inset?: boolean }
>(({ className, inset, ...props }, ref) => (
  <div
    ref={ref}
    className={cn(
      "dropdown-header fw-semibold",
      inset && "ps-4",
      className
    )}
    {...props}
  />
))
DropdownMenuLabel.displayName = "DropdownMenuLabel"

const DropdownMenuSeparator = React.forwardRef<
  HTMLHRElement,
  React.HTMLAttributes<HTMLHRElement>
>(({ className, ...props }, ref) => (
  <hr
    ref={ref}
    className={cn("dropdown-divider", className)}
    {...props}
  />
))
DropdownMenuSeparator.displayName = "DropdownMenuSeparator"

const DropdownMenuShortcut = React.forwardRef<
  HTMLSpanElement,
  React.HTMLAttributes<HTMLSpanElement>
>(({ className, ...props }, ref) => (
  <span
    ref={ref}
    className={cn("ms-auto text-xs opacity-60", className)}
    {...props}
  />
))
DropdownMenuShortcut.displayName = "DropdownMenuShortcut"

export {
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuShortcut,
}
