"use client"

import * as React from "react"
import { cn } from "@/lib/utils"

interface SelectContextType {
  value: string
  onValueChange: (value: string) => void
  open: boolean
  onOpenChange: (open: boolean) => void
}

const SelectContext = React.createContext<SelectContextType | undefined>(undefined)

interface SelectProps {
  children: React.ReactNode
  value?: string
  onValueChange?: (value: string) => void
  open?: boolean
  onOpenChange?: (open: boolean) => void
}

const Select: React.FC<SelectProps> = ({ 
  children, 
  value = "", 
  onValueChange, 
  open = false, 
  onOpenChange 
}) => {
  const [selectedValue, setSelectedValue] = React.useState(value)
  const [isOpen, setIsOpen] = React.useState(open)
  
  React.useEffect(() => {
    setSelectedValue(value)
  }, [value])
  
  React.useEffect(() => {
    setIsOpen(open)
  }, [open])
  
  const handleValueChange = (newValue: string) => {
    setSelectedValue(newValue)
    onValueChange?.(newValue)
    setIsOpen(false)
    onOpenChange?.(false)
  }
  
  const handleOpenChange = (newOpen: boolean) => {
    setIsOpen(newOpen)
    onOpenChange?.(newOpen)
  }
  
  return (
    <SelectContext.Provider value={{ 
      value: selectedValue, 
      onValueChange: handleValueChange,
      open: isOpen,
      onOpenChange: handleOpenChange
    }}>
      {children}
    </SelectContext.Provider>
  )
}

interface SelectValueProps {
  placeholder?: string
  className?: string
}

const SelectValue: React.FC<SelectValueProps> = ({ placeholder, className }) => {
  const context = React.useContext(SelectContext)
  
  return (
    <span className={className}>
      {context?.value || placeholder}
    </span>
  )
}

const SelectTrigger = React.forwardRef<
  HTMLButtonElement,
  React.ButtonHTMLAttributes<HTMLButtonElement>
>(({ className, children, ...props }, ref) => {
  const context = React.useContext(SelectContext)
  
  return (
    <button
      ref={ref}
      type="button"
      className={cn(
        "form-select d-flex align-items-center justify-content-between border bg-white",
        className
      )}
      onClick={() => context?.onOpenChange(!context.open)}
      {...props}
    >
      {children}
      <svg 
        className="ms-2" 
        width="16" 
        height="16" 
        viewBox="0 0 24 24" 
        fill="none" 
        stroke="currentColor" 
        strokeWidth="2"
      >
        <polyline points="6,9 12,15 18,9"></polyline>
      </svg>
    </button>
  )
})
SelectTrigger.displayName = "SelectTrigger"

interface SelectContentProps extends React.HTMLAttributes<HTMLDivElement> {
  children: React.ReactNode
}

const SelectContent = React.forwardRef<HTMLDivElement, SelectContentProps>(
  ({ className, children, ...props }, ref) => {
    const context = React.useContext(SelectContext)
    
    if (!context?.open) return null
    
    return (
      <div
        ref={ref}
        className={cn("dropdown-menu show border shadow position-absolute w-100", className)}
        {...props}
      >
        {children}
      </div>
    )
  }
)
SelectContent.displayName = "SelectContent"

interface SelectItemProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  value: string
  children: React.ReactNode
}

const SelectItem = React.forwardRef<HTMLButtonElement, SelectItemProps>(
  ({ className, children, value, ...props }, ref) => {
    const context = React.useContext(SelectContext)
    const isSelected = context?.value === value
    
    return (
      <button
        ref={ref}
        type="button"
        className={cn(
          "dropdown-item d-flex align-items-center gap-2 border-0 bg-transparent w-100 text-start",
          isSelected && "active",
          className
        )}
        onClick={() => context?.onValueChange(value)}
        {...props}
      >
        {isSelected && (
          <svg 
            width="16" 
            height="16" 
            viewBox="0 0 24 24" 
            fill="none" 
            stroke="currentColor" 
            strokeWidth="2"
          >
            <polyline points="20,6 9,17 4,12"></polyline>
          </svg>
        )}
        {children}
      </button>
    )
  }
)
SelectItem.displayName = "SelectItem"

const SelectLabel = React.forwardRef<
  HTMLDivElement,
  React.HTMLAttributes<HTMLDivElement>
>(({ className, ...props }, ref) => (
  <div
    ref={ref}
    className={cn("dropdown-header fw-semibold", className)}
    {...props}
  />
))
SelectLabel.displayName = "SelectLabel"

const SelectSeparator = React.forwardRef<
  HTMLHRElement,
  React.HTMLAttributes<HTMLHRElement>
>(({ className, ...props }, ref) => (
  <hr
    ref={ref}
    className={cn("dropdown-divider", className)}
    {...props}
  />
))
SelectSeparator.displayName = "SelectSeparator"

export {
  Select,
  SelectValue,
  SelectTrigger,
  SelectContent,
  SelectLabel,
  SelectItem,
  SelectSeparator,
}
